<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }

define('CRM_FILE', __DIR__ . '/../data/crm.json');

function loadCRM() {
    if (!file_exists(CRM_FILE)) {
        $empty = ['customers'=>[],'appointments'=>[],'notes'=>[],'next_customer_id'=>1,'next_appointment_id'=>1,'next_note_id'=>1];
        file_put_contents(CRM_FILE, json_encode($empty, JSON_PRETTY_PRINT));
        return $empty;
    }
    return json_decode(file_get_contents(CRM_FILE), true);
}

function saveCRM($data) {
    file_put_contents(CRM_FILE, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

function respond($data, $code = 200) {
    http_response_code($code);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$resource = $_GET['resource'] ?? '';
$id = isset($_GET['id']) ? (int)$_GET['id'] : null;
$body = json_decode(file_get_contents('php://input'), true) ?? [];

$crm = loadCRM();

// ─── CUSTOMERS ────────────────────────────────────────────────────────────────
if ($resource === 'customers') {
    if ($method === 'GET') {
        if ($id) {
            $found = array_values(array_filter($crm['customers'], fn($c) => $c['id'] === $id));
            respond($found ? $found[0] : null);
        }
        $q = strtolower($_GET['q'] ?? '');
        $list = $crm['customers'];
        if ($q) {
            $list = array_values(array_filter($list, function($c) use ($q) {
                return str_contains(strtolower($c['name'] ?? ''), $q)
                    || str_contains(strtolower($c['phone'] ?? ''), $q)
                    || str_contains(strtolower($c['email'] ?? ''), $q);
            }));
        }
        usort($list, fn($a,$b) => strcmp($b['created_at'] ?? '', $a['created_at'] ?? ''));
        respond(['customers' => $list, 'total' => count($list)]);
    }

    if ($method === 'POST') {
        $customer = [
            'id'          => $crm['next_customer_id']++,
            'name'        => trim($body['name'] ?? ''),
            'phone'       => trim($body['phone'] ?? ''),
            'email'       => trim($body['email'] ?? ''),
            'dob'         => $body['dob'] ?? '',
            'skin_type'   => $body['skin_type'] ?? '',
            'concerns'    => $body['concerns'] ?? [],
            'source'      => $body['source'] ?? '',
            'tags'        => $body['tags'] ?? [],
            'city'        => $body['city'] ?? '',
            'total_spent' => 0,
            'visit_count' => 0,
            'last_visit'  => null,
            'status'      => 'active',
            'created_at'  => date('c'),
        ];
        if (!$customer['name'] || !$customer['phone']) respond(['error'=>'Name and phone are required'], 422);
        $crm['customers'][] = $customer;
        saveCRM($crm);
        respond($customer, 201);
    }

    if ($method === 'PUT' && $id) {
        foreach ($crm['customers'] as &$c) {
            if ($c['id'] === $id) {
                foreach (['name','phone','email','dob','skin_type','concerns','source','tags','city','status'] as $f) {
                    if (array_key_exists($f, $body)) $c[$f] = $body[$f];
                }
                $c['updated_at'] = date('c');
                saveCRM($crm);
                respond($c);
            }
        }
        respond(['error'=>'Not found'], 404);
    }

    if ($method === 'DELETE' && $id) {
        $crm['customers'] = array_values(array_filter($crm['customers'], fn($c) => $c['id'] !== $id));
        saveCRM($crm);
        respond(['success'=>true]);
    }
}

// ─── APPOINTMENTS ─────────────────────────────────────────────────────────────
if ($resource === 'appointments') {
    if ($method === 'GET') {
        $cid = isset($_GET['customer_id']) ? (int)$_GET['customer_id'] : null;
        $list = $crm['appointments'];
        if ($cid) $list = array_values(array_filter($list, fn($a) => $a['customer_id'] === $cid));
        usort($list, fn($a,$b) => strcmp($b['date'], $a['date']));
        $cmap = [];
        foreach ($crm['customers'] as $c) $cmap[$c['id']] = $c['name'];
        foreach ($list as &$a) $a['customer_name'] = $cmap[$a['customer_id']] ?? 'Unknown';
        respond(['appointments' => $list]);
    }

    if ($method === 'POST') {
        $apt = [
            'id'          => $crm['next_appointment_id']++,
            'customer_id' => (int)($body['customer_id'] ?? 0),
            'service'     => trim($body['service'] ?? ''),
            'date'        => $body['date'] ?? '',
            'time'        => $body['time'] ?? '',
            'duration'    => (int)($body['duration'] ?? 60),
            'price'       => (float)($body['price'] ?? 0),
            'status'      => $body['status'] ?? 'scheduled',
            'note'        => $body['note'] ?? '',
            'created_at'  => date('c'),
        ];
        if (!$apt['customer_id'] || !$apt['service'] || !$apt['date']) respond(['error'=>'customer_id, service, date required'], 422);
        $crm['appointments'][] = $apt;
        foreach ($crm['customers'] as &$c) {
            if ($c['id'] === $apt['customer_id']) {
                $c['visit_count'] = ($c['visit_count'] ?? 0) + 1;
                if ($apt['status'] === 'completed') {
                    $c['total_spent'] = ($c['total_spent'] ?? 0) + $apt['price'];
                    $c['last_visit'] = $apt['date'];
                }
            }
        }
        saveCRM($crm);
        respond($apt, 201);
    }

    if ($method === 'PUT' && $id) {
        foreach ($crm['appointments'] as &$a) {
            if ($a['id'] === $id) {
                $prev = $a['status'];
                foreach (['service','date','time','duration','price','status','note'] as $f) {
                    if (array_key_exists($f, $body)) $a[$f] = $body[$f];
                }
                if ($prev !== 'completed' && $a['status'] === 'completed') {
                    foreach ($crm['customers'] as &$c) {
                        if ($c['id'] === $a['customer_id']) {
                            $c['total_spent'] = ($c['total_spent'] ?? 0) + $a['price'];
                            $c['last_visit'] = $a['date'];
                        }
                    }
                }
                $a['updated_at'] = date('c');
                saveCRM($crm);
                respond($a);
            }
        }
        respond(['error'=>'Not found'], 404);
    }

    if ($method === 'DELETE' && $id) {
        $crm['appointments'] = array_values(array_filter($crm['appointments'], fn($a) => $a['id'] !== $id));
        saveCRM($crm);
        respond(['success'=>true]);
    }
}

// ─── NOTES ────────────────────────────────────────────────────────────────────
if ($resource === 'notes') {
    if ($method === 'GET') {
        $cid = isset($_GET['customer_id']) ? (int)$_GET['customer_id'] : null;
        $list = $crm['notes'];
        if ($cid) $list = array_values(array_filter($list, fn($n) => $n['customer_id'] === $cid));
        usort($list, fn($a,$b) => strcmp($b['created_at'], $a['created_at']));
        respond(['notes' => $list]);
    }

    if ($method === 'POST') {
        $note = [
            'id'          => $crm['next_note_id']++,
            'customer_id' => (int)($body['customer_id'] ?? 0),
            'text'        => trim($body['text'] ?? ''),
            'type'        => $body['type'] ?? 'general',
            'created_at'  => date('c'),
        ];
        if (!$note['customer_id'] || !$note['text']) respond(['error'=>'customer_id and text required'], 422);
        $crm['notes'][] = $note;
        saveCRM($crm);
        respond($note, 201);
    }

    if ($method === 'DELETE' && $id) {
        $crm['notes'] = array_values(array_filter($crm['notes'], fn($n) => $n['id'] !== $id));
        saveCRM($crm);
        respond(['success'=>true]);
    }
}

// ─── STATS ────────────────────────────────────────────────────────────────────
if ($resource === 'stats') {
    $customers = $crm['customers'];
    $apts = $crm['appointments'];
    $today = date('Y-m-d');
    $thisMonth = date('Y-m');

    $totalRevenue = array_sum(array_map(fn($a) => $a['status']==='completed' ? (float)$a['price'] : 0, $apts));
    $monthRevenue = array_sum(array_map(fn($a) => ($a['status']==='completed' && str_starts_with($a['date'], $thisMonth)) ? (float)$a['price'] : 0, $apts));
    $todayApts = count(array_filter($apts, fn($a) => $a['date'] === $today));
    $activeCustomers = count(array_filter($customers, fn($c) => ($c['status'] ?? 'active') === 'active'));
    $newThisMonth = count(array_filter($customers, fn($c) => str_starts_with($c['created_at'] ?? '', $thisMonth)));

    $services = [];
    foreach ($apts as $a) {
        if ($a['status'] === 'completed') {
            $services[$a['service']] = ($services[$a['service']] ?? 0) + 1;
        }
    }
    arsort($services);
    $topServices = array_slice($services, 0, 5, true);

    $chart = [];
    for ($i = 5; $i >= 0; $i--) {
        $m = date('Y-m', strtotime("-$i months"));
        $rev = array_sum(array_map(fn($a) => ($a['status']==='completed' && str_starts_with($a['date'], $m)) ? (float)$a['price'] : 0, $apts));
        $chart[] = ['month' => $m, 'revenue' => $rev];
    }

    respond([
        'total_customers'  => count($customers),
        'active_customers' => $activeCustomers,
        'new_this_month'   => $newThisMonth,
        'total_revenue'    => $totalRevenue,
        'month_revenue'    => $monthRevenue,
        'today_appointments' => $todayApts,
        'top_services'     => $topServices,
        'revenue_chart'    => $chart,
    ]);
}

respond(['error' => 'Unknown resource'], 404);

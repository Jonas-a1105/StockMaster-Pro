<?php
require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Gate;
use App\Domain\Enums\Capability;
use App\Domain\Enums\UserPlan;
use App\Domain\Enums\UserRole;

echo "--- Iniciando verificación del Sistema RBAC ---\n";

$testCases = [
    [
        'name' => 'Free User accessing POS',
        'plan' => UserPlan::FREE->value,
        'role' => UserRole::USUARIO->value,
        'capability' => Capability::ACCESS_POS,
        'expected' => false
    ],
    [
        'name' => 'Premium User accessing POS',
        'plan' => UserPlan::PREMIUM->value,
        'role' => UserRole::USUARIO->value,
        'capability' => Capability::ACCESS_POS,
        'expected' => true
    ],
    [
        'name' => 'Free Admin accessing Config',
        'plan' => UserPlan::FREE->value,
        'role' => UserRole::ADMIN->value,
        'capability' => Capability::MANAGE_CONFIG,
        'expected' => true
    ],
    [
        'name' => 'Premium User accessing Reports',
        'plan' => UserPlan::PREMIUM->value,
        'role' => UserRole::USUARIO->value,
        'capability' => Capability::VIEW_REPORTS,
        'expected' => true
    ],
    [
        'name' => 'Free User accessing Reports',
        'plan' => UserPlan::FREE->value,
        'role' => UserRole::USUARIO->value,
        'capability' => Capability::VIEW_REPORTS,
        'expected' => false
    ]
];

$successCount = 0;
foreach ($testCases as $case) {
    $result = Gate::allows($case['plan'], $case['role'], $case['capability']);
    $status = ($result === $case['expected']) ? "✅" : "❌";
    echo "$status {$case['name']}: " . ($result ? 'Permitido' : 'Denegado') . "\n";
    if ($result === $case['expected']) $successCount++;
}

if ($successCount === count($testCases)) {
    echo "\n✅ VERIFICACIÓN EXITOSA: El motor de permisos (Gate) funciona correctamente.\n";
} else {
    echo "\n❌ ERROR: Se detectaron fallos en la lógica de permisos.\n";
}

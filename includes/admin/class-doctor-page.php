<?php

namespace DWW_Fingerprinting;

if (!defined('ABSPATH')) {
    exit;
}

class Doctor_Page
{
    public static function render(): void
    {
        if (!current_user_can('manage_options')) {
            wp_die(__('No tienes permisos suficientes.'));
        }

        $repair_result = self::handle_repair_request();
        $health = Health_Check::run();

?>
        <div class="wrap dww-doctor-page">

            <h1>Diagnóstico del sistema</h1>

            <p>
                Comprueba el estado del entorno, el almacenamiento,
                la base de datos y el motor documental de DWW Fingerprinting.
            </p>

            <?php self::render_repair_notice($repair_result); ?>

            <?php self::render_toolbar($health); ?>

            <?php self::render_summary($health); ?>

            <?php self::render_checks($health['checks'] ?? []); ?>

        </div>
    <?php
    }

    private static function render_toolbar(array $health): void
    {
        $has_repairable_errors = false;

        foreach (($health['checks'] ?? []) as $check) {
            if (
                ($check['status'] ?? '') !== 'ok' &&
                self::is_repairable((string) ($check['id'] ?? ''))
            ) {
                $has_repairable_errors = true;
                break;
            }
        }

        echo '<div style="margin:16px 0;display:flex;gap:8px;align-items:center;flex-wrap:wrap;">';

        echo '<form method="post">';
        wp_nonce_field('dww_run_doctor');

        echo '<button type="submit" name="dww_run_doctor" value="1" class="button button-primary">';
        echo '🩺 Ejecutar diagnóstico';
        echo '</button>';

        echo '</form>';

        if ($has_repairable_errors) {
            echo '<span class="button button-secondary">🛠 Hay reparaciones disponibles</span>';
        } else {
            echo '<span class="button button-secondary disabled" aria-disabled="true">Sin reparaciones pendientes</span>';
        }

        echo '</div>';
    }

    private static function render_summary(array $health): void
    {
        $score = (int) ($health['score'] ?? 0);
        $checks = $health['checks'] ?? [];

        $total = count($checks);
        $ok = 0;
        $warnings = 0;
        $errors = 0;

        foreach ($checks as $check) {
            $status = (string) ($check['status'] ?? '');
            $severity = (string) ($check['severity'] ?? '');

            if ($status === 'ok') {
                $ok++;
                continue;
            }

            if ($severity === 'warning') {
                $warnings++;
            } else {
                $errors++;
            }
        }

        $label = self::score_label($score);
        $color = self::score_color($score);

        echo '<div class="dww-doctor-summary">';

        echo '<h2>Estado general</h2>';

        echo '<p class="dww-doctor-score" style="color:' .
            esc_attr($color) .
            ';">' .
            esc_html((string) $score) .
            '/100</p>';

        echo '<p><strong>' . esc_html($label) . '</strong></p>';

        echo '<div class="dww-doctor-progress">';

        echo '<div class="dww-doctor-progress-bar" style="width:' .
            esc_attr((string) $score) .
            '%;background:' .
            esc_attr($color) .
            ';"></div>';

        echo '</div>';

        echo '<p>';
        echo esc_html((string) $total) . ' comprobaciones · ';
        echo esc_html((string) $ok) . ' correctas · ';
        echo esc_html((string) $warnings) . ' advertencias · ';
        echo esc_html((string) $errors) . ' errores';
        echo '</p>';

        echo '</div>';
    }

    private static function render_checks(array $checks): void
    {
        if (empty($checks)) {
            Admin_UI::empty_state(
                'No hay comprobaciones registradas.',
                'El sistema no ha devuelto ningún diagnóstico.'
            );
            return;
        }

        echo '<h2>Comprobaciones</h2>';

        echo '<div class="dww-doctor-checks">';

        foreach ($checks as $check) {
            self::render_check_card($check);
        }

        echo '</div>';
    }

    private static function render_check_card(array $check): void
    {
        $status = (string) ($check['status'] ?? '');
        $severity = (string) ($check['severity'] ?? '');
        $check_id = (string) ($check['id'] ?? '');

        $color = self::severity_color($status, $severity);
        $icon = self::severity_icon($status, $severity);

        echo '<div class="dww-doctor-check" style="border-left-color:' .
            esc_attr($color) .
            ';">';

        echo '<h3>' .
            esc_html($icon . ' ' . (string) ($check['name'] ?? 'Check')) .
            '</h3>';

        echo '<p><strong>' .
            esc_html((string) ($check['message'] ?? '')) .
            '</strong></p>';

        if (!empty($check['description'])) {
            echo '<p>' . esc_html((string) $check['description']) . '</p>';
        }

        if (!empty($check['fix'])) {
            echo '<p><strong>Acción recomendada:</strong> ' .
                esc_html((string) $check['fix']) .
                '</p>';
        }

        if (
            $status !== 'ok' &&
            $check_id !== '' &&
            self::is_repairable($check_id)
        ) {
            echo self::repair_button($check_id);
        }

        if (!empty($check['category'])) {
            echo '<p class="dww-doctor-category">' .
                esc_html((string) $check['category']) .
                '</p>';
        }

        echo '</div>';
    }

    private static function handle_repair_request(): ?array
    {
        if (
            empty($_POST['dww_repair_check']) ||
            empty($_POST['check_id'])
        ) {
            return null;
        }

        check_admin_referer('dww_repair_check');

        $check_id = sanitize_key(
            (string) $_POST['check_id']
        );

        return Health_Check_Manager::repair($check_id);
    }

    private static function render_repair_notice(?array $repair_result): void
    {
        if ($repair_result === null) {
            return;
        }

        $success = !empty($repair_result['success']);

        echo '<div class="notice ' .
            ($success ? 'notice-success' : 'notice-error') .
            '"><p><strong>';

        echo $success
            ? 'Reparación ejecutada.'
            : 'No se pudo ejecutar la reparación.';

        echo '</strong> ' .
            esc_html((string) ($repair_result['message'] ?? '')) .
            '</p></div>';
    }

    private static function is_repairable(string $check_id): bool
    {
        Health_Check_Registry::register();

        foreach (Health_Check_Manager::all() as $check) {
            $result = $check->run();

            if (($result['id'] ?? '') !== $check_id) {
                continue;
            }

            return $check instanceof Repairable_Health_Check_Interface;
        }

        return false;
    }

    private static function repair_button(string $check_id): string
    {
        ob_start();

    ?>
        <form method="post" class="dww-doctor-repair-form">
            <?php wp_nonce_field('dww_repair_check'); ?>

            <input
                type="hidden"
                name="check_id"
                value="<?php echo esc_attr($check_id); ?>">

            <button
                type="submit"
                name="dww_repair_check"
                value="1"
                class="button button-secondary">
                🛠 Reparar automáticamente
            </button>
        </form>
<?php

        return (string) ob_get_clean();
    }

    private static function score_label(int $score): string
    {
        if ($score >= 90) {
            return 'Sistema saludable';
        }

        if ($score >= 60) {
            return 'Sistema con advertencias';
        }

        return 'Sistema con errores críticos';
    }

    private static function score_color(int $score): string
    {
        if ($score >= 90) {
            return '#2e7d32';
        }

        if ($score >= 60) {
            return '#f9a825';
        }

        return '#c62828';
    }

    private static function severity_color(string $status, string $severity): string
    {
        if ($status === 'ok') {
            return '#2e7d32';
        }

        return match ($severity) {
            'warning' => '#f9a825',
            'info' => '#2271b1',
            default => '#c62828',
        };
    }

    private static function severity_icon(string $status, string $severity): string
    {
        if ($status === 'ok') {
            return '🟢';
        }

        return match ($severity) {
            'warning' => '🟠',
            'info' => '🔵',
            default => '🔴',
        };
    }
}

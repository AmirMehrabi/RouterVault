<?php

namespace App\Services\Backups;

use App\Models\BackupToken;
use App\Models\Router;

class RouterPushScriptGenerator
{
    public function generate(Router $router): ?string
    {
        $token = BackupToken::where('router_id', $router->id)
            ->where('is_active', true)
            ->first();

        if (! $token) {
            $token = BackupToken::generateForRouter($router);
        }

        $baseUrl = config('app.url', 'https://cloud.skybase.app');
        $uploadUrl = $baseUrl.'/api/v1/backups/upload';

        return <<<'SCRIPT'
# RouterVault - Router Backup Push Script
# Generated for: ROUTER_NAME
# Run this script on your RouterOS device to enable automatic config backups
#
# Usage:
#   1. Copy this entire script
#   2. Open Winbox or SSH into your router
#   3. Paste and run the script
#
# The script will:
#   - Export your configuration (secrets hidden by default)
#   - Upload it to RouterVault via HTTPS
#   - Store a versioned backup for diff comparison

# Step 1: Create the backup file
/tool fetch url="UPLOAD_URL" \
    http-method=post \
    http-data=":rsc file content" \
    http-header-field="Authorization: Bearer TOKEN" \
    as-value

# Alternative method using /export and upload:
/system scheduler add name="skybase-backup" \
    interval=1d \
    on-event="\
        /export file=skybase-backup; \
        /tool fetch url=\"UPLOAD_URL\" \
            http-method=post \
            upload-file=skybase-backup.rsc \
            http-header-field=\"Authorization: Bearer TOKEN\" \
            as-value; \
        /file remove skybase-backup.rsc; \
    " \
    comment="RouterVault automatic backup" \
    disabled=no

SCRIPT;
    }

    public function generateForDisplay(Router $router): array
    {
        $token = BackupToken::where('router_id', $router->id)
            ->where('is_active', true)
            ->first();

        if (! $token) {
            $token = BackupToken::generateForRouter($router);
        }

        $baseUrl = config('app.url', 'https://cloud.skybase.app');
        $uploadUrl = $baseUrl.'/api/v1/backups/upload';

        $script = $this->buildScript($router, $token->token, $uploadUrl);

        return [
            'script' => $script,
            'token' => $token->token,
            'upload_url' => $uploadUrl,
        ];
    }

    protected function buildScript(Router $router, string $token, string $uploadUrl): string
    {
        $safeUploadUrl = addslashes($uploadUrl);

        return <<<SCRIPT
# RouterVault - Router Backup Push Script
# Router: {$router->name}
# Generated: {now()->format('Y-m-d H:i:s')}

# Create scheduler entry for daily backups
/system scheduler add name="skybase-daily-backup" \\
    interval=1d \\
    start-date=now \\
    on-event="{
        /export file=skybase-export;
        /tool fetch url=\\"{$safeUploadUrl}\\" \\
            http-method=post \\
            upload-file=skybase-export.rsc \\
            http-header-field=\\"Authorization: Bearer {$token}\\" \\
            as-value;
        /file remove skybase-export.rsc;
    }" \\
    comment="RouterVault automatic daily backup" \\
    disabled=no

# Run first backup immediately
/export file=skybase-export
/tool fetch url="{$safeUploadUrl}" \\
    http-method=post \\
    upload-file=skybase-export.rsc \\
    http-header-field="Authorization: Bearer {$token}" \\
    as-value
/file remove skybase-export.rsc

# Backup script installed successfully!
# Next backup will run in 24 hours.
# You can also run manually: /system scheduler run [find name="skybase-daily-backup"]
SCRIPT;
    }
}

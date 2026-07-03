<?php

namespace App\Http\Middleware;

use App\Models\ActivityLog;
use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Symfony\Component\HttpFoundation\Response;

class AuditUserAction
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($request->user() && in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            $routeModel = collect($request->route()?->parameters() ?? [])
                ->first(fn (mixed $parameter): bool => $parameter instanceof Model);

            ActivityLog::query()->create([
                'tenant_id' => $request->user()->tenant_id,
                'user_id' => $request->user()->id,
                'action' => $request->route()?->getName() ?? strtolower($request->method()).':'.$request->path(),
                'model_type' => $routeModel?->getMorphClass(),
                'model_id' => $routeModel?->getKey(),
                'new_values' => [
                    'method' => $request->method(),
                    'status' => $response->getStatusCode(),
                    'input' => $this->safeInput($request),
                ],
                'ip_address' => $request->ip(),
                'user_agent' => str($request->userAgent())->limit(500)->toString(),
            ]);
        }

        return $response;
    }

    /**
     * @return array<string, mixed>
     */
    protected function safeInput(Request $request): array
    {
        return collect($request->except(['_token', '_method']))
            ->map(fn (mixed $value, string $key): mixed => $this->sanitizeValue($value, $key))
            ->all();
    }

    protected function sanitizeValue(mixed $value, string $key): mixed
    {
        $sensitive = ['password', 'private_key', 'secret', 'telegram_bot_token', 'token'];

        if (collect($sensitive)->contains(fn (string $field): bool => str_contains(strtolower($key), $field))) {
            return '[REDACTED]';
        }

        if ($value instanceof UploadedFile) {
            return ['name' => $value->getClientOriginalName(), 'size' => $value->getSize()];
        }

        if (is_array($value)) {
            return collect($value)
                ->map(fn (mixed $nestedValue, string|int $nestedKey): mixed => $this->sanitizeValue($nestedValue, (string) $nestedKey))
                ->all();
        }

        if (is_string($value)) {
            return str($value)->limit(500)->toString();
        }

        return is_scalar($value) || $value === null ? $value : get_debug_type($value);
    }
}

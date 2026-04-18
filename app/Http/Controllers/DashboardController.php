<?php

namespace App\Http\Controllers;

use App\Models\AccessPoint;
use App\Models\Router;
use App\Models\WirelessClient;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $routers = Router::query()
            ->orderBy('name')
            ->get([
                'id',
                'name',
                'site',
                'status',
                'cpu_usage',
                'memory_usage',
                'active_sessions_count',
                'total_customers',
                'uptime',
                'location',
                'updated_at',
            ]);

        $accessPoints = AccessPoint::query()
            ->with(['router:id,name', 'site:id,name'])
            ->orderBy('name')
            ->get([
                'id',
                'router_id',
                'site_id',
                'name',
                'ssid',
                'band',
                'status',
                'ip_address',
                'signal_quality',
                'channel_utilization',
                'connected_clients_count',
                'last_seen_at',
                'location',
                'updated_at',
            ]);

        $wirelessClients = WirelessClient::query()
            ->with(['accessPoint:id,name,ssid,band', 'site:id,name', 'router:id,name'])
            ->latest('last_seen_at')
            ->get([
                'id',
                'access_point_id',
                'router_id',
                'site_id',
                'host_name',
                'mac_address',
                'ssid',
                'band',
                'signal_strength',
                'signal_to_noise',
                'tx_ccq',
                'rx_ccq',
                'is_connected',
                'last_ip_address',
                'last_seen_at',
                'last_management_status',
                'last_management_message',
            ]);

        return view('dashboard', [
            'dashboard' => [
                'overview' => $this->buildOverview($routers, $accessPoints, $wirelessClients),
                'charts' => $this->buildCharts($routers, $accessPoints, $wirelessClients),
                'highlights' => $this->buildHighlights($routers, $accessPoints, $wirelessClients),
                'tables' => $this->buildTables($routers, $accessPoints, $wirelessClients),
            ],
        ]);
    }

    /**
     * @param  EloquentCollection<int, Router>  $routers
     * @param  EloquentCollection<int, AccessPoint>  $accessPoints
     * @param  EloquentCollection<int, WirelessClient>  $wirelessClients
     * @return array<string, mixed>
     */
    protected function buildOverview(EloquentCollection $routers, EloquentCollection $accessPoints, EloquentCollection $wirelessClients): array
    {
        $onlineRouters = $routers->where('status', 'online')->count();
        $onlineAccessPoints = $accessPoints->where('status', 'online')->count();
        $connectedClients = $wirelessClients->where('is_connected', true)->count();
        $provisionedClients = $wirelessClients->filter(fn (WirelessClient $client): bool => $client->isProvisioned())->count();

        $routerCpuAverage = (int) round($routers->avg(fn (Router $router): int => (int) ($router->cpu_usage ?? 0)) ?? 0);
        $routerMemoryAverage = (int) round($routers->avg(fn (Router $router): int => (int) ($router->memory_usage ?? 0)) ?? 0);
        $signalAverage = (int) round($wirelessClients->avg(fn (WirelessClient $client): int => max(0, min(100, (int) (($client->signal_strength ?? -100) + 100)))) ?? 0);
        $clientCcqAverage = (int) round($wirelessClients->avg(fn (WirelessClient $client): int => (int) max($client->tx_ccq ?? 0, $client->rx_ccq ?? 0)) ?? 0);

        return [
            'stats' => [
                [
                    'label' => 'Routers online',
                    'value' => $onlineRouters,
                    'detail' => $routers->count().' total routers',
                    'meta' => $routers->count() > 0 ? $this->percentage($onlineRouters, $routers->count()).'% availability' : 'No routers yet',
                    'tone' => 'sky',
                ],
                [
                    'label' => 'Access points online',
                    'value' => $onlineAccessPoints,
                    'detail' => $accessPoints->count().' total APs',
                    'meta' => $accessPoints->count() > 0 ? $this->percentage($onlineAccessPoints, $accessPoints->count()).'% radio availability' : 'No access points yet',
                    'tone' => 'emerald',
                ],
                [
                    'label' => 'Connected wireless clients',
                    'value' => $connectedClients,
                    'detail' => $wirelessClients->count().' discovered clients',
                    'meta' => $wirelessClients->count() > 0 ? $this->percentage($connectedClients, $wirelessClients->count()).'% currently active' : 'No wireless clients yet',
                    'tone' => 'amber',
                ],
                [
                    'label' => 'Provisioned clients',
                    'value' => $provisionedClients,
                    'detail' => $wirelessClients->count() - $provisionedClients.' still missing credentials',
                    'meta' => $wirelessClients->count() > 0 ? $this->percentage($provisionedClients, $wirelessClients->count()).'% ready for management' : 'No provisioning data yet',
                    'tone' => 'rose',
                ],
            ],
            'health' => [
                [
                    'label' => 'Avg router CPU',
                    'value' => $routerCpuAverage,
                    'suffix' => '%',
                    'tone' => $this->performanceTone($routerCpuAverage, inverse: true),
                ],
                [
                    'label' => 'Avg router memory',
                    'value' => $routerMemoryAverage,
                    'suffix' => '%',
                    'tone' => $this->performanceTone($routerMemoryAverage, inverse: true),
                ],
                [
                    'label' => 'Avg client signal',
                    'value' => $signalAverage,
                    'suffix' => '%',
                    'tone' => $this->performanceTone($signalAverage),
                ],
                [
                    'label' => 'Avg client CCQ',
                    'value' => $clientCcqAverage,
                    'suffix' => '%',
                    'tone' => $this->performanceTone($clientCcqAverage),
                ],
            ],
        ];
    }

    /**
     * @param  EloquentCollection<int, Router>  $routers
     * @param  EloquentCollection<int, AccessPoint>  $accessPoints
     * @param  EloquentCollection<int, WirelessClient>  $wirelessClients
     * @return array<string, mixed>
     */
    protected function buildCharts(EloquentCollection $routers, EloquentCollection $accessPoints, EloquentCollection $wirelessClients): array
    {
        $capacityBySite = $wirelessClients
            ->groupBy(fn (WirelessClient $client): string => $client->site?->name ?: $client->accessPoint?->name ?: 'Unassigned')
            ->map(function (Collection $clients, string $site) use ($accessPoints): array {
                $siteAccessPoints = $accessPoints->filter(function (AccessPoint $accessPoint) use ($site): bool {
                    $accessPointSite = $accessPoint->site?->name ?: $accessPoint->name;

                    return $accessPointSite === $site;
                });

                $capacity = max($siteAccessPoints->sum('connected_clients_count'), $clients->count(), 1);

                return [
                    'label' => $site,
                    'value' => $clients->where('is_connected', true)->count(),
                    'capacity' => $capacity,
                ];
            })
            ->sortByDesc('value')
            ->take(6)
            ->values();

        $bandDistribution = collect(['2.4GHz', '5GHz', '6GHz', 'Dual/Other'])
            ->map(function (string $label) use ($accessPoints): array {
                $count = $accessPoints->filter(function (AccessPoint $accessPoint) use ($label): bool {
                    $band = strtolower((string) $accessPoint->band);

                    return match ($label) {
                        '2.4GHz' => str_contains($band, '2.4'),
                        '5GHz' => str_contains($band, '5'),
                        '6GHz' => str_contains($band, '6'),
                        default => $band === '' || (! str_contains($band, '2.4') && ! str_contains($band, '5') && ! str_contains($band, '6')),
                    };
                })->count();

                return [
                    'label' => $label,
                    'value' => $count,
                ];
            })
            ->filter(fn (array $item): bool => $item['value'] > 0)
            ->values();

        $routerLoad = $routers
            ->map(fn (Router $router): array => [
                'label' => $router->name,
                'cpu' => (int) ($router->cpu_usage ?? 0),
                'memory' => (int) ($router->memory_usage ?? 0),
            ])
            ->sortByDesc(fn (array $router): int => max($router['cpu'], $router['memory']))
            ->take(6)
            ->values();

        $signalBuckets = collect([
            ['label' => 'Excellent', 'min' => -60, 'max' => 0],
            ['label' => 'Good', 'min' => -70, 'max' => -61],
            ['label' => 'Fair', 'min' => -80, 'max' => -71],
            ['label' => 'Weak', 'min' => -200, 'max' => -81],
        ])->map(function (array $bucket) use ($wirelessClients): array {
            $count = $wirelessClients->filter(function (WirelessClient $client) use ($bucket): bool {
                $signal = $client->signal_strength;

                if ($signal === null) {
                    return false;
                }

                return $signal >= $bucket['min'] && $signal <= $bucket['max'];
            })->count();

            return [
                'label' => $bucket['label'],
                'value' => $count,
            ];
        })->values();

        $managementStatuses = collect([
            ['label' => 'Connected', 'value' => $wirelessClients->where('is_connected', true)->count()],
            ['label' => 'Disconnected', 'value' => $wirelessClients->where('is_connected', false)->count()],
            ['label' => 'Managed OK', 'value' => $wirelessClients->where('last_management_status', 'success')->count()],
            ['label' => 'Mgmt issues', 'value' => $wirelessClients->where('last_management_status', 'failed')->count()],
        ])->filter(fn (array $item): bool => $item['value'] > 0)->values();

        return [
            'capacityBySite' => $capacityBySite,
            'bandDistribution' => $bandDistribution,
            'routerLoad' => $routerLoad,
            'signalBuckets' => $signalBuckets,
            'managementStatuses' => $managementStatuses,
        ];
    }

    /**
     * @param  EloquentCollection<int, Router>  $routers
     * @param  EloquentCollection<int, AccessPoint>  $accessPoints
     * @param  EloquentCollection<int, WirelessClient>  $wirelessClients
     * @return array<string, mixed>
     */
    protected function buildHighlights(EloquentCollection $routers, EloquentCollection $accessPoints, EloquentCollection $wirelessClients): array
    {
        $busiestAccessPoint = $accessPoints
            ->sortByDesc(fn (AccessPoint $accessPoint): int => (int) ($accessPoint->connected_clients_count ?? 0))
            ->first();

        $topRouter = $routers
            ->sortByDesc(fn (Router $router): int => (int) ($router->active_sessions_count ?? 0))
            ->first();

        $weakClients = $wirelessClients
            ->filter(fn (WirelessClient $client): bool => ($client->signal_strength ?? 0) <= -80)
            ->count();

        $staleAccessPoints = $accessPoints
            ->filter(fn (AccessPoint $accessPoint): bool => $accessPoint->last_seen_at?->lt(now()->subHours(6)) ?? true)
            ->count();

        return [
            'hero' => [
                'title' => 'Network visibility that reflects your live modules',
                'subtitle' => 'This dashboard now reads router, access point, and wireless client records directly from your tenant-scoped data.',
                'items' => [
                    [
                        'label' => 'Busiest AP',
                        'value' => $busiestAccessPoint?->name ?: 'No access points yet',
                        'detail' => $busiestAccessPoint ? (($busiestAccessPoint->connected_clients_count ?? 0).' connected clients') : 'Add access points to unlock radio capacity insights',
                    ],
                    [
                        'label' => 'Top session router',
                        'value' => $topRouter?->name ?: 'No routers yet',
                        'detail' => $topRouter ? (($topRouter->active_sessions_count ?? 0).' active sessions') : 'Router session metrics will appear here',
                    ],
                    [
                        'label' => 'Weak-signal clients',
                        'value' => $weakClients,
                        'detail' => 'Clients at -80 dBm or worse that may need repositioning or AP tuning',
                    ],
                    [
                        'label' => 'APs needing attention',
                        'value' => $staleAccessPoints,
                        'detail' => 'Access points offline or not seen in the last 6 hours',
                    ],
                ],
            ],
        ];
    }

    /**
     * @param  EloquentCollection<int, Router>  $routers
     * @param  EloquentCollection<int, AccessPoint>  $accessPoints
     * @param  EloquentCollection<int, WirelessClient>  $wirelessClients
     * @return array<string, mixed>
     */
    protected function buildTables(EloquentCollection $routers, EloquentCollection $accessPoints, EloquentCollection $wirelessClients): array
    {
        return [
            'topRouters' => $routers
                ->sortByDesc(fn (Router $router): int => (int) ($router->active_sessions_count ?? 0))
                ->take(5)
                ->map(fn (Router $router): array => [
                    'id' => $router->id,
                    'name' => $router->name,
                    'site' => $router->site ?: $router->location ?: 'Unassigned',
                    'status' => $router->status ?: 'offline',
                    'sessions' => (int) ($router->active_sessions_count ?? 0),
                    'cpu' => (int) ($router->cpu_usage ?? 0),
                    'memory' => (int) ($router->memory_usage ?? 0),
                    'href' => route('routers.show', $router),
                ])
                ->values(),
            'topAccessPoints' => $accessPoints
                ->sortByDesc(fn (AccessPoint $accessPoint): int => (int) ($accessPoint->connected_clients_count ?? 0))
                ->take(5)
                ->map(fn (AccessPoint $accessPoint): array => [
                    'id' => $accessPoint->id,
                    'name' => $accessPoint->name,
                    'router' => $accessPoint->router?->name ?: 'No router',
                    'site' => $accessPoint->site?->name ?: $accessPoint->location ?: 'Unassigned',
                    'band' => $accessPoint->band ?: 'Unknown',
                    'clients' => (int) ($accessPoint->connected_clients_count ?? 0),
                    'quality' => (int) ($accessPoint->signal_quality ?? 0),
                    'href' => route('access-points.show', $accessPoint),
                ])
                ->values(),
            'attentionClients' => $wirelessClients
                ->filter(fn (WirelessClient $client): bool => ! $client->is_connected || ($client->signal_strength ?? 0) <= -80 || $client->last_management_status === 'failed')
                ->sortBy(function (WirelessClient $client): int {
                    if ($client->last_management_status === 'failed') {
                        return -1000;
                    }

                    return (int) ($client->signal_strength ?? -200) * -1;
                })
                ->take(6)
                ->map(fn (WirelessClient $client): array => [
                    'id' => $client->id,
                    'name' => $client->host_name ?: $client->mac_address,
                    'access_point' => $client->accessPoint?->name ?: 'No AP',
                    'site' => $client->site?->name ?: 'Unassigned',
                    'signal' => $client->signal_strength,
                    'status' => $client->is_connected ? 'connected' : 'disconnected',
                    'management_status' => $client->last_management_status,
                    'message' => $client->last_management_message,
                    'last_seen' => $client->last_seen_at?->diffForHumans(),
                    'href' => route('wireless-clients.show', $client),
                ])
                ->values(),
        ];
    }

    protected function percentage(int $value, int $total): int
    {
        if ($total === 0) {
            return 0;
        }

        return (int) round(($value / $total) * 100);
    }

    protected function performanceTone(int $value, bool $inverse = false): string
    {
        if ($inverse) {
            return match (true) {
                $value >= 80 => 'rose',
                $value >= 60 => 'amber',
                default => 'emerald',
            };
        }

        return match (true) {
            $value >= 80 => 'emerald',
            $value >= 60 => 'amber',
            default => 'rose',
        };
    }
}

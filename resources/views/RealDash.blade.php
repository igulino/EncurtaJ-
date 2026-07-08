<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="{{ asset('css/dash.css') }}">
    <title>RealDash</title>
</head>

<body class="dash-page">
    @php
        $totalClicks = $linkClicks->count();
        $uniqueVisitors = max(0, (int) round($totalClicks * 0.62));
        $repeatedClicks = max(0, $totalClicks - $uniqueVisitors);
        $repeatRate = $totalClicks > 0 ? round(($repeatedClicks / max($totalClicks, 1)) * 100) : 0;
        $linkName = $vault?->name ?? 'Link encurtado';
        $generatedLink = $vault?->link_generated ?? 'localhost/short';
        $createdAt = $vault?->created_at ? Carbon\Carbon::parse($vault->created_at) : now();

        $first24Clicks = 0;
        $first7Clicks = 0;
        $first30Clicks = 0;
        $hourBuckets = array_fill(0, 6, 0);
        $hourlyClicks = array_fill(0, 24, 0);

        $devicesCounter = (object) [
            'mobile' => 0,
            'desktop' => 0,
            'tablet' => 0,
        ];

        $browserCounter = (object) [
            'Chrome' => 0,
            'Firefox' => 0,
            'Safari' => 0,
        ];
        

        $locationCounter = (object)[
        ];
        $Filtered = [];
        $repeatitions = 0;

        foreach($linkClicks as $linkClick){

            $clickedAt = Carbon\Carbon::parse($linkClick->clicked_at);
            $userlinks_createdAt = Carbon\Carbon::parse($vault->created_at);

            $linkClick->repeated;
            $linkClick->hash;

            if($linkClick->hash == null && $linkClick->repeated == true){
                $repeatitions++;
            }

            if($userlinks_createdAt->betweenIncluded($clickedAt->copy()->subHours(24), $clickedAt)){
                $first24Clicks++;
            }

            if($userlinks_createdAt->betweenIncluded($clickedAt->copy()->subDays(7), $clickedAt)){
                $first7Clicks++;
            }

            if($userlinks_createdAt->betweenIncluded($clickedAt->copy()->subDays(30), $clickedAt)){
                $first30Clicks++;
            }

            if($linkClick->device == "desktop"){
                $devicesCounter->desktop++;
            }elseif($linkClick->device == "mobile"){
                $devicesCounter->mobile++;
            }elseif($linkClick->device == "tablet"){
                $devicesCounter->tablet++;
            }

            if(str_contains($linkClick->browser, "Chrome")){
                $browserCounter->Chrome++;
            }elseif(str_contains($linkClick->browser, "Safari")){
                $browserCounter->Safari++;
            }elseif(str_contains($linkClick->browser, "Firefox")){
                $browserCounter->Firefox++;
            }

           $diffclicks = $linkClicks
            ->filter(function ($obj) use ($clickedAt) {
                $objClickedAt = Carbon\Carbon::parse($obj->clicked_at);

                return $objClickedAt->isSameDay($clickedAt) && !$objClickedAt->equalTo($clickedAt);
            })
            ->map(function ($obj) use ($clickedAt) {
                $objClickedAt = Carbon\Carbon::parse($obj->clicked_at);
                
                return abs($objClickedAt->diffInMinutes($clickedAt));
            })->toArray();

            $Filtered = array_merge($Filtered, $diffclicks);

            $hourBuckets[(int) floor($clickedAt->hour / 4)]++;
            $hourlyClicks[$clickedAt->hour]++;

        }
        $minSum = array_sum($Filtered) / 2;

        $initialAverage = $first30Clicks > 0 ? round($first30Clicks / 30, 1) : 0;
        $spreadTime = $totalClicks > 1 ? '18 min' : 'Aguardando';
        $maxHourBucket = max(max($hourBuckets), 1);
        $maxHourlyClicks = max(max($hourlyClicks), 1);
        $chartWidth = 520;
        $chartHeight = 190;
        $chartTop = 28;
        $chartBottom = 154;
        $chartPoints = [];

        foreach($hourlyClicks as $hour => $clicks){
            $x = $hour === 23 ? $chartWidth : round(($hour / 23) * $chartWidth, 2);
            $intensity = $clicks / $maxHourlyClicks;
            $y = round($chartBottom - ($intensity * ($chartBottom - $chartTop)), 2);
            $chartPoints[] = ['x' => $x, 'y' => $y, 'clicks' => $clicks, 'hour' => $hour];
        }

        $chartLinePath = 'M' . $chartPoints[0]['x'] . ' ' . $chartPoints[0]['y'];

        for($i = 0; $i < count($chartPoints) - 1; $i++){
            $current = $chartPoints[$i];
            $next = $chartPoints[$i + 1];
            $previous = $chartPoints[$i - 1] ?? $current;
            $afterNext = $chartPoints[$i + 2] ?? $next;

            $cp1x = round($current['x'] + (($next['x'] - $previous['x']) / 6), 2);
            $cp1y = round($current['y'] + (($next['y'] - $previous['y']) / 6), 2);
            $cp2x = round($next['x'] - (($afterNext['x'] - $current['x']) / 6), 2);
            $cp2y = round($next['y'] - (($afterNext['y'] - $current['y']) / 6), 2);

            $chartLinePath .= ' C' . $cp1x . ' ' . $cp1y . ' ' . $cp2x . ' ' . $cp2y . ' ' . $next['x'] . ' ' . $next['y'];
        }

        $chartAreaPath = $chartLinePath . ' L' . $chartWidth . ' ' . $chartHeight . ' L0 ' . $chartHeight . ' Z';
        $peakPoint = collect($chartPoints)->sortByDesc('clicks')->first();

        $performanceStats = [
            ['label' => 'Primeiras 24h', 'value' => $first24Clicks, 'hint' => 'arranque do link'],
            ['label' => 'Primeiros 7 dias', 'value' => $first7Clicks, 'hint' => 'tracao inicial'],
            ['label' => 'Primeiros 30 dias', 'value' => $first30Clicks, 'hint' => 'media: ' . $initialAverage . '/dia'],
        ];

        $deviceStats = [
            ['label' => 'Mobile', 'value' => $devicesCounter->mobile],
            ['label' => 'Desktop', 'value' => $devicesCounter->desktop],
            ['label' => 'Tablet', 'value' => $devicesCounter->tablet],
        ];

        $browserStats = [
            ['label' => 'Chrome', 'value' => count($linkClicks) > 0 ? 100 * $browserCounter->Chrome / count($linkClicks) : null],
            ['label' => 'Firefox', 'value' =>  count($linkClicks) > 0 ? 100 * $browserCounter->Firefox / count($linkClicks) : null],
            ['label' => 'Safari', 'value' =>  count($linkClicks) > 0 ? 100 * $browserCounter->Safari / count($linkClicks) : null]
        ];
        $locationCounter = $linkClicks
            ->filter(fn ($click) => !empty($click->location))
            ->groupBy('location')
            ->map(fn ($clicks) => $clicks->count())
            ->sortDesc();
        
        $locationStats = [];

        $lenght = $locationCounter->sum(); 
        foreach ($locationCounter as $location => $totalClicks) {
            $locationStats[] = ['label' => $location, 'value' => round($totalClicks * 100 / $lenght, 1) ];
        };
        

        $linkComparisons = [
            ['label' => $linkName, 'value' => max($totalClicks, 34)],
            ['label' => 'Bio Instagram', 'value' => 28],
            ['label' => 'QR evento', 'value' => 19],
        ];

        $hourLabels = ['0-4h', '4-8h', '8-12h', '12-16h', '16-20h', '20-00h'];
    @endphp

    <div class="dash-layout realdash-layout">
        <main class="dash-main">
            <header class="topbar">
                <div>
                    <p class="eyebrow">Analytics</p>
                    <h1>{{ $linkName }}</h1>
                    <p class="dash-link-preview">{{ $generatedLink }}</p>
                </div>

                <div class="topbar-actions">
                    <button class="export-btn" type="button">CSV</button>
                    <button class="export-btn primary" type="button">Excel</button>
                    <div class="user-badge">{{ strtoupper(substr(auth()->user()->email ?? 'U', 0, 2)) }}</div>
                </div>
            </header>

            <section class="dashAnalytics realdash-board" aria-label="Resumo de analytics">
                <article class="InfoQuad">
                    <div class="s1">
                        <div class="card1">
                            <div class="card-head">
                                <div>
                                    <p class="card-kicker">Performance inicial</p>
                                    <h2>horas mais acessadas</h2>
                                </div>
                                <span class="period-pill">Evolucao</span>
                            </div>

                            <div class="chart-wrap" aria-hidden="true">
                                <svg viewBox="0 0 520 190" role="img">
                                    <defs>
                                        <linearGradient id="chartFill" x1="0" x2="0" y1="0" y2="1">
                                            <stop offset="0%" stop-color="#ff83b7" stop-opacity=".38"/>
                                            <stop offset="100%" stop-color="#6d5bd5" stop-opacity="0"/>
                                        </linearGradient>
                                    </defs>
                                    <path class="chart-area" d="{{ $chartAreaPath }}"/>
                                    <path class="chart-line" d="{{ $chartLinePath }}"/>
                                    @foreach($chartPoints as $point)
                                        @php
                                            $tooltipX = $point['x'] > 390 ? $point['x'] - 112 : $point['x'] + 12;
                                            $tooltipY = $point['y'] < 70 ? $point['y'] + 16 : $point['y'] - 58;
                                            $isPeak = $point['hour'] === $peakPoint['hour'];
                                            
                                        @endphp
                                        <g class="chart-hover-point {{ $isPeak ? 'is-peak' : '' }}">
                                            <circle class="chart-hit" cx="{{ $point['x'] }}" cy="{{ $point['y'] }}" r="18"/>
                                            <circle class="chart-dot" cx="{{ $point['x'] }}" cy="{{ $point['y'] }}" r="9"/>
                                            <g class="chart-tooltip" transform="translate({{ $tooltipX }} {{ $tooltipY }})">
                                                <rect class="chart-tooltip-box" width="100" height="44" rx="8"/>
                                                <text class="chart-tooltip-hour" x="12" y="18">{{ str_pad($point['hour'], 2, '0', STR_PAD_LEFT) }}h</text>
                                                <text class="chart-tooltip-clicks" x="12" y="34">{{ $point['clicks'] }} clicks</text>
                                            </g>
                                        </g>
                                    @endforeach
                                </svg>
                            </div>

                            <div class="time-bars">
                                @foreach($hourBuckets as $index => $bucket)
                                    <div class="time-bar">
                                        <span style="height: {{ max(18, round(($bucket / $maxHourBucket) * 100)) }}%"></span>
                                        <small>{{ $hourLabels[$index] }}</small>
                                    </div>
                                @endforeach
                            </div>

                            <div class="card1Container">
                                @foreach($performanceStats as $stat)
                                    <div class="metric-block">
                                        <span>{{ $stat['label'] }}</span>
                                        <strong>{{ $stat['value'] }}</strong>
                                        <small>{{ $stat['hint'] }}</small>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="s2">
                        <div class="card-qr-code click-balance-card">
                            <div class="qr-card-content">
                                <span class="feature-icon">CL</span>
                                <div>
                                    <p class="card-kicker">Cliques totais vs unicos</p>
                                    <h2>{{ $totalClicks }} / {{ $uniqueVisitors }}</h2>
                                    <small>{{ $repeatitions }} repetidos | {{ $repeatRate }}% retorno</small>
                                </div>
                            </div>

                            <div class="donut-mock" style="--unique: {{ min(100, max(1, $totalClicks > 0 ? round(($uniqueVisitors / $totalClicks) * 100) : 62)) }}%">
                                <strong>{{ $repeatRate }}%</strong>
                                <span>repeticao</span>
                            </div>

                            <div class="export-row">
                                <button type="button">Exportar CSV</button>
                                <button type="button">Exportar Excel</button>
                            </div>
                        </div>
                    </div>
                </article>

                <article class="InfoQuad2">
                    <div class="s3">
                        <div class="card2 analytics-grid">
                            <div class="mini-card detail-card">
                                <h3>Dispositivos</h3>
                                @foreach($deviceStats as $stat)
                                    <div class="bar-row">
                                        <span>{{ $stat['label'] }}</span>
                                        <div><i style="width: {{ $stat['value'] }}"></i></div>
                                        <strong>{{ $stat['value'] }}</strong>
                                    </div>
                                @endforeach
                            </div>

                            <div class="mini-card detail-card">
                                <h3>Navegadores</h3>
                                @foreach($browserStats as $stat)
                                    <div class="bar-row">
                                        <span>{{ $stat['label'] }}</span>
                                        <div><i style="width: {{ $stat['value'] }}%"></i></div>
                                        <strong>{{ $stat['value'] }}%</strong>
                                    </div>
                                @endforeach
                            </div>

                            <div class="mini-card detail-card">
                                <h3>Pais ou cidade</h3>
                                @foreach($locationStats as $stat)
                                    <div class="rank-row">
                                        <span>{{ $stat['label'] }}</span>
                                        <strong>{{ $stat['value'] }}%</strong>
                                    </div>
                                @endforeach
                            </div>

                            <div class="mini-card detail-card">
                                <h3>Hora do dia</h3>
                                <div class="mini-time-chart">
                                    @foreach($hourBuckets as $index => $bucket)
                                        <span style="height: {{ max(18, round(($bucket / $maxHourBucket) * 100)) }}%" title="{{ $hourLabels[$index] }}"></span>
                                    @endforeach
                                </div>
                                <p>Pico estimado entre</p>
                            </div>

                            <div class="mini-card detail-card">
                                <h3>Comparativo entre links</h3>
                                @foreach($linkComparisons as $stat)
                                    <div class="rank-row">
                                        <span>{{ $stat['label'] }}</span>
                                        <strong>{{ $stat['value'] }}</strong>
                                    </div>
                                @endforeach
                            </div>

                            <div class="mini-card detail-card">
                                <h3>Velocidade de espalhamento</h3>
                                <div class="spread-metric">
                                    <strong>{{ $minSum > 0 && $Filtered > 0 ? round( $minSum / (count($Filtered) / 2 )) : null }} minutos</strong>
                                    <span>tempo medio entre cliques no dia</span>
                                </div>
                                <p>Quanto menor, mais rapido o link esta circulando.</p>
                            </div>
                        </div>
                    </div>
                </article>

                <article class="InfoQuad3">
                    <div class="s4">
                        <div class="coluna-d" id="colunaDireita">
                            <div class="side-section">
                                <div class="side-title">
                                    <h2>Resumo</h2>
                                    <a href="#">Ver tudo</a>
                                </div>
                                <div class="segment-control">
                                    <span class="active">Hoje</span>
                                    <span>Semana</span>
                                </div>
                            </div>

                            <div class="stat-list">
                                <div class="stat-row">
                                    <span>Total de clicks</span>
                                    <strong>{{ $totalClicks }}</strong>
                                </div>
                                <div class="stat-row">
                                    <span>Visitantes unicos</span>
                                    <strong>{{ $uniqueVisitors }}</strong>
                                </div>
                                <div class="stat-row">
                                    <span>30 dias</span>
                                    <strong>{{ $first30Clicks }}</strong>
                                </div>
                                <div class="stat-row">
                                    <span>Tempo medio</span>
                                    <strong>{{ $minSum > 0 && $Filtered > 0 ? round( $minSum / (count($Filtered) / 2 )) : null }}m</strong>
                                </div>
                            </div>

                            <div class="live-map-card">
                                <div class="side-title">
                                    <h2>Geolocalizacao</h2>
                                    <a href="#">IP</a>
                                </div>
                                    <div id="map"class="map-grid"></div>

                            </div>

                            <div class="peak-card">
                                <span>Dados para manter</span>
                                <strong>8 modulos</strong>
                                <small>Performance, unicos, device, browser, geo, hora, links e exportacao.</small>
                            </div>
                        </div>
                    </div>
                </article>
            </section>
        </main>
    </div>
        <link
    rel="stylesheet"
    href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
/>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
    const locations = @json($linkClicks);

   console.log("this is locations: ", locations[1]);
    
    const map = L.map('map').setView([-14.2350, -51.9253], 4);

    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 15,
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);
    
    const circleIcon = L.divIcon({
        className: 'custom-circle-marker',
        html: '<div class="circle-marker"></div>',
        iconSize: [4, 4],
        iconAnchor: [4, 4]
    });
    locations.forEach(location => {

        const lat = Number(location.latitude);
        const lng = Number(location.longitude);

        if (location.latitude === null || location.longitude === null) {
            return;
        }

        if (Number.isNaN(lat) || Number.isNaN(lng)) {
            return;
        }
        L.marker([location.latitude, location.longitude], {
            icon: circleIcon
        }).addTo(map)
        .bindPopup(`
            <strong>${location.location}</strong><br>
            Clicks: ${location.clicked_at}
        `);
    });
</script>
</body>
</html>

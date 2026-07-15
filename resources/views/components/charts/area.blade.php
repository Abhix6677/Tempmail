@props([
    "chartId",
    "chartData",
    "function",
    "color",
    "height" => 250,
])

<div class="relative px-2 pb-2" x-data="chartComponent('{{ $chartId }}', '{{ $color }}', '{{ $height }}', @js($chartData))" x-init="init()" x-on:update-chart.window="if ($event.detail.chartId === chartId) updateChart($event.detail.chartData)" wire:ignore>
    <div :id="chartId"></div>

    <div class="absolute inset-0 flex items-center justify-center bg-white/50 dark:bg-gray-900/50 z-10 h-full" wire:loading wire:target="{{ $function }}">
        <div class="flex items-center justify-center w-full h-full">
            <i class="hgi hgi-stroke hgi-loading-03 animate-spin text-3xl"></i>
        </div>
    </div>
</div>

@once
    <script>
        function chartComponent(chartId, color, height, initialData) {
            return {
                chartId,
                chart: null,
                chartData: initialData,
                initialized: false,
                color,
                height,

                init() {
                    Alpine.nextTick(() => {
                        if (this.initialized) return;

                        if (this.chartData?.x?.length && this.chartData?.y?.length) {
                            this.renderChart(this.chartData);
                            this.initialized = true;
                        } else {
                            // Retry once after short delay
                            setTimeout(() => {
                                if (this.chartData?.x?.length && this.chartData?.y?.length && !this.initialized) {
                                    this.renderChart(this.chartData);
                                    this.initialized = true;
                                }
                            }, 50);
                        }
                    });
                },

                updateChart(newData) {
                    this.chartData = newData;

                    if (this.chart) {
                        this.chart.updateOptions(
                            {
                                xaxis: { categories: newData.x },
                                series: [{ name: 'Emails', data: newData.y }],
                            },
                            false,
                            true
                        );
                    } else {
                        this.renderChart(newData);
                        this.initialized = true;
                    }
                },

                renderChart(data) {
                    const el = document.getElementById(chartId);
                    if (!el || !data?.x?.length || !data?.y?.length) return;

                    const options = {
                        chart: {
                            type: 'area',
                            height: this.height,
                            toolbar: { show: false },
                            zoom: { enabled: false },
                            foreColor: 'inherit',
                            background: 'transparent',
                        },
                        stroke: { curve: 'smooth', width: 3 },
                        colors: [this.color],
                        fill: {
                            type: 'gradient',
                            gradient: {
                                shadeIntensity: 1,
                                opacityFrom: 0.35,
                                opacityTo: 0.15,
                                stops: [0, 100],
                            },
                        },
                        xaxis: {
                            categories: data.x,
                            tooltip: { enabled: false },
                            labels: {
                                style: {
                                    colors: '#6b7280',
                                    fontSize: '12px',
                                    fontWeight: 500,
                                    fontFamily: 'inherit',
                                },
                            },
                            axisBorder: { show: false },
                            axisTicks: { show: false },
                        },
                        yaxis: {
                            labels: {
                                style: {
                                    colors: '#6b7280',
                                    fontFamily: 'inherit',
                                },
                                formatter: function (val) {
                                    if (val >= 1000000) return (val / 1000000).toFixed(1).replace(/\.0$/, '') + 'M';
                                    if (val >= 1000) return (val / 1000).toFixed(1).replace(/\.0$/, '') + 'K';
                                    return val.toLocaleString();
                                },
                            },
                        },
                        tooltip: {
                            theme: 'light',
                            x: { show: false },
                            style: {
                                fontSize: '14px',
                                fontFamily: 'inherit',
                            },
                            custom: function ({ series, seriesIndex, dataPointIndex, w }) {
                                const value = series[seriesIndex][dataPointIndex];
                                const label = w.globals.categoryLabels[w.globals.labels[dataPointIndex] - 1];
                                return `
                                    <div style="
                                        background: #ffffff;
                                        color: #111827;
                                        padding: 0.5rem 0.75rem;
                                        border-radius: 1rem;
                                        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.05);
                                        font-family: inherit;
                                    ">
                                        <div style="font-size: 0.65rem;">${label}</div>
                                        <div style="font-size: 0.85rem;">${value.toLocaleString()}</div>
                                    </div>
                                `;
                            },
                        },
                        dataLabels: { enabled: false },
                        grid: {
                            borderColor: 'rgba(107, 114, 128, 0.15)',
                            strokeDashArray: 4,
                            xaxis: { lines: { show: false } },
                            yaxis: { lines: { show: true } },
                        },
                        series: [
                            {
                                name: 'Emails',
                                data: data.y,
                            },
                        ],
                    };

                    this.chart = new ApexCharts(el, options);
                    this.chart.render();
                },
            };
        }
    </script>
@endonce

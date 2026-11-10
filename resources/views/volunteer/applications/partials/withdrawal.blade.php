                <details @if($status === 'submitted') open @endif class="vd-withdrawal rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <summary class="cursor-pointer rounded-2xl p-5 text-sm font-semibold text-slate-600 focus:outline-none focus:ring-2 focus:ring-indigo-500">Kelola penarikan lamaran</summary>
                    <div class="px-4 pb-4"><x-application-selection :application="$application" /></div>
                </details>

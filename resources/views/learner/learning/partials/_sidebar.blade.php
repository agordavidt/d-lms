{{--
    Partial: resources/views/learner/learning/partials/_sidebar.blade.php
    Receives: $enrollment, $allWeekProgress, $currentWeek, $stats
--}}
<aside class="sidebar" id="sidebar">
    <div class="sidebar-inner">

        {{-- Program header + progress --}}
        <div class="px-4 pt-5 pb-3 border-b border-slate-100">
            <p class="text-[11px] font-black uppercase tracking-widest text-slate-400 mb-1">
                {{ $enrollment->program->name }}
            </p>
            <div class="flex items-center gap-2 mt-2">
                <div class="flex-1 h-1.5 bg-slate-100 rounded-full overflow-hidden">
                    <div class="h-full bg-blue-600 rounded-full prog-fill"
                         style="width: {{ $stats['overall_progress'] }}%"></div>
                </div>
                <span class="progress-pill">{{ $stats['overall_progress'] }}%</span>
            </div>
            <p class="text-[11px] text-slate-400 mt-1">
                {{ $stats['completed_weeks'] }} / {{ $stats['total_weeks'] }} weeks complete
            </p>
        </div>

        {{-- Week + content tree --}}
        @foreach($allWeekProgress as $wp)
        @php
            $week         = $wp->moduleWeek;
            $isLocked     = !$wp->is_unlocked;
            $isDone       = $wp->is_completed;
            $isCurrent    = $week->id === $currentWeek->id;
            $weekContents = $week->publishedContents()
                ->with(['contentProgress' => fn($q) => $q->where('user_id', auth()->id())->where('enrollment_id', $enrollment->id)])
                ->orderBy('order')
                ->get();
        @endphp

        <div class="week-row {{ $isCurrent ? 'open' : '' }}"
             id="week-row-{{ $week->id }}"
             onclick="toggleWeek({{ $week->id }}, {{ $isLocked ? 'true' : 'false' }})">
            <div class="flex items-center gap-3 px-4 py-3.5">

                @if($isLocked)
                    <svg class="w-4 h-4 text-slate-300 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                @elseif($isDone)
                    <svg class="w-4 h-4 text-green-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                @else
                    <div class="w-4 h-4 rounded-full border-2 {{ $isCurrent ? 'border-blue-600' : 'border-slate-300' }} flex-shrink-0"></div>
                @endif

                <div class="flex-1 min-w-0">
                    <p class="text-[13px] font-bold text-slate-800 leading-snug truncate">
                        {{ $week->programModule->title ?? '' }} · Week {{ $week->week_number }}
                    </p>
                    <p class="text-[11px] text-slate-400 truncate">{{ $week->title }}</p>
                </div>

                @if(!$isLocked)
                <svg class="w-3.5 h-3.5 text-slate-400 flex-shrink-0 chevron-{{ $week->id }} {{ $isCurrent ? 'rotate-180' : '' }} transition-transform"
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
                @endif
            </div>
        </div>

        <div id="week-contents-{{ $week->id }}" class="{{ $isCurrent ? '' : 'hidden' }}">
            @foreach($weekContents as $content)
            @php
                $cp   = $content->contentProgress->first();
                $done = $cp && $cp->is_completed;
                $icon = match($content->content_type) {
                    'video'   => '▶', 'article' => '📄',
                    'pdf'     => '📎', 'quiz'    => '✏️', default => '•',
                };
            @endphp
            <div class="content-item {{ $done ? 'done' : '' }}"
                 id="sidebar-item-{{ $content->id }}"
                 onclick="loadContent({{ $content->id }})">
                <div class="w-4 h-4 rounded-full flex-shrink-0 mt-0.5 flex items-center justify-center {{ $done ? 'bg-green-500' : 'border border-slate-300' }}">
                    @if($done)
                    <svg class="w-2.5 h-2.5 text-white" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                    </svg>
                    @endif
                </div>
                <div class="min-w-0 flex-1">
                    <p class="item-title text-[12px] text-slate-700 leading-snug line-clamp-2">{{ $content->title }}</p>
                    <p class="text-[11px] text-slate-400 mt-0.5 flex items-center gap-1">
                        <span>{{ $icon }}</span>
                        <span>{{ ucfirst($content->content_type) }}</span>
                        @if($content->video_duration_minutes)
                        <span>· {{ $content->video_duration_minutes }} min</span>
                        @endif
                    </p>
                </div>
            </div>
            @endforeach

            @if($week->assessment)
            @php $submitted = $week->assessment->attempts->isNotEmpty(); @endphp
            <div class="content-item {{ $submitted ? 'done' : '' }}"
                 id="sidebar-item-assessment-{{ $week->assessment->id }}"
                 onclick="loadAssessment({{ $week->assessment->id }})">
                <div class="w-4 h-4 rounded-full flex-shrink-0 mt-0.5 flex items-center justify-center {{ $submitted ? 'bg-green-500' : 'border border-slate-300' }}">
                    @if($submitted)
                    <svg class="w-2.5 h-2.5 text-white" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                    </svg>
                    @endif
                </div>
                <div class="min-w-0">
                    <p class="item-title text-[12px] text-slate-700 leading-snug">Week Assessment</p>
                    <p class="text-[11px] text-slate-400 mt-0.5">✏️ Quiz · {{ $week->assessment->questions->count() }} questions</p>
                </div>
            </div>
            @endif
        </div>

        @endforeach
    </div>
</aside>
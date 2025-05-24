<div style="margin-right:10px" x-data="{ open: false }" class="relative" @keydown.escape.window="open = false" @click.away="open = false">

@php
    $priority = $notification->data['priority'] ?? 'medium'; // default to medium
    $colors = [
        'high' => 'color: red;',
        'medium' => 'color: goldenrod;',
        'low' => 'color: green;',
    ];
@endphp
    <!-- Notification Bell / Trigger -->
    <button 
        @click="open = !open; if(open) $wire.markAsRead()" 
        class="relative flex items-center p-2 rounded-md hover:bg-gray-100 focus:outline-none focus:ring focus:ring-indigo-500"
        aria-haspopup="true" aria-expanded="open.toString()"
    >
        <svg class="w-6 h-6 text-gray-600" style="width:35px" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                  d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-5-5.917V5a2 2 0 10-4 0v.083A6.002 6.002 0 004 11v3.159c0 .538-.214 1.055-.595 1.436L2 17h5m7 0v1a3 3 0 11-6 0v-1m6 0H9" />
        </svg>

        @if($this->notifications->whereNull('read_at')->count() > 0)
            <!-- unread badge -->
            <span class="absolute top-0 right-0 inline-flex items-center justify-center px-1.5 py-0.5 text-xs font-bold leading-none text-white bg-red-600 rounded-full" v style="padding:2px">
                {{ $this->notifications->whereNull('read_at')->count() }}
            </span>
        @endif
    </button>

    <!-- Dropdown Panel -->
    <div 
        x-show="open" 
        x-transition 
        class="origin-top-right absolute right-0 mt-2 w-[500px] rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 z-50"
        style="display: none; width:160px"
    >
        <div class="py-2 max-h-96 overflow-y-auto">
            @if($this->notifications->isEmpty())
                <p class="px-4 py-2 text-gray-500">No notifications found.</p>
            @else
                <ul>
                    @foreach($this->notifications as $notification)
                        <li class="px-4 py-2 hover:bg-gray-100 cursor-pointer border-b last:border-b-0">
                            <span class="w-2.5 h-2.5 rounded-full"  style="{{ $colors[$priority] ?? '' }}">
                                {{ $notification->data['message'] ?? json_encode($notification->data) }}
                            </span>    
                            <strong>{{ $notification->data['title'] ?? json_encode($notification->data) }}</strong>

                            @if(is_null($notification->read_at))
                                <span class="inline-block ml-2 px-1 py-0.5 text-xs font-semibold text-white bg-blue-600 rounded">New</span>
                            @endif
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>
</div>

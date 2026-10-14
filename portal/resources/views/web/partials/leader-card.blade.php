{{-- Leadership committee card: photo (or initials), name, designation. --}}
<div class="card leader-card">
    <div class="card-body text-center">
        @if ($m->photo)
            <x-img :image="$m->photo" alt="" class="leader-photo" sizes="96px" />
        @else
            <span class="leader-photo leader-initials" aria-hidden="true">{{ $m->initials() }}</span>
        @endif
        <h3 class="leader-name">{{ $m->t('name') }}</h3>
        <p class="muted leader-role">{{ $m->t('designation') }}</p>
    </div>
</div>

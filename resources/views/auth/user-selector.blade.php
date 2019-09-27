@if ($hasSudoed)
    @if ($originalUser)


        <form action="{{ route('sudosu.return') }}" method="post">
            {!! csrf_field() !!}
            <button class="btn btn-danger btn-sm" type="submit">
                Return to {{$originalUser->name}}</button>
        </form>
    @endif
@endif

@if( Auth::user()->can('is_admin')||$user->hasSudoed())
    @if (!$hasSudoed)
        <form action="{{ route('sudosu.login_as_user') }}" method="post">
            <input type="hidden" name="userId" value="{{ $user->id }}">
            <input type="hidden" name="originalUserId" value="{{ $originalUser->id ?? null }}">

            {!! csrf_field() !!}
            <button class="btn btn-danger btn-sm" type="submit">Log in as this user</button>
        </form>
    @endif
@endif


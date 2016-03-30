<h3>Committees</h3>

@if(count($user->committeesFilter('current')) > 0)

    <ul class="list-group">
        @foreach($user->committeesFilter('current') as $committee)
            <li class="list-group-item">
                <strong>
                    {{ $committee->name }}
                </strong>
                @if($committee->pivot->edition != null)
                    {{ $committee->pivot->edition }}
                @endif
                <br>
                <sub>As {{$committee->pivot->role}} since {{$committee->pivot->start}}</sub>
            </li>
        @endforeach
    </ul>

@else

    <p>
        Currently not a member of a committee.
    </p>

@endif
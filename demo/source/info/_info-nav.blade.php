<ul>
    @foreach($infoPages as $page)
        <li><a href="{{ $page['url'] }}">{{ $page['name'] }}</a></li>
    @endforeach
</ul>

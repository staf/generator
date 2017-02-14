@extends('layout')
@section('content')
    <h1 class="title">Hello!</h1>
    <p>This is a static site with a bird!</p>
    <img src="/img/bird.jpg" alt="Bird">
    <hr>
    <p>Here are some random posts.</p>
    @foreach($posts as $post)
        <article class="post">
            <h3 class="post-title">{{ $post['title'] }}</h3>
            <p>{{ $post['body'] }}</p>
        </article>
    @endforeach
@endsection

@extends('layouts.default')

@section('title', "Phragile - {$project->title}")

@section('content')
	@include('project.partials.settings_form')

	<h1>{{ $project->title }}</h1>
	<p>
		@if(Auth::check())
			{!! link_to_route(
				'create_sprint_path',
				'Add a new sprint',
				['project' => $project->slug]
			) !!}
		@else
			There are no sprints for this project yet.
		@endif
	</p>
@stop

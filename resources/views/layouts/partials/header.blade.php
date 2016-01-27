<nav class="navbar navbar-inverse" role="navigation">
	<div class="container">
		<div class="navbar-header">
			<a class="navbar-brand" href="{!! url() !!}">
				<img src="{{ URL::asset('/images/phragile_logo_white.svg') }}" alt="Phragile logo"/>
			</a>
		</div>

		<ul class="nav navbar-nav navbar-right">
			@if(Auth::check())
				<li class="dropdown">
					<a href="#" class="dropdown-toggle" data-toggle="dropdown">
						Logged in as {!! Auth::user()->username !!}
						<span class="caret"></span>
					</a>
					<ul class="dropdown-menu" role="menu">
						<li>
							{!! link_to(
								'#',
								'Conduit API Token',
								[
									'id' => 'conduit-api-token',
									'data-toggle' => 'modal',
									'data-target' => '#conduit-modal',
								]
							) !!}
						</li>
						@if(Auth::check() && Auth::user()->isInAdminList(env('PHRAGILE_ADMINS')))
							<li>{!! link_to_route('stats', 'Statistics') !!}</li>
						@endif
						@if(in_array(Route::currentRouteName(), ['project_path', 'sprint_path', 'sprint_live_path', 'snapshot_path']) && isset($project))
							<li>{!! link_to_route('create_sprint_path', 'New sprint', isset($project) ? $project->slug : $sprint->project->slug) !!}</li>
							<li><a id="project-settings" href="#" data-toggle="modal" data-target="#project-settings-modal">Project settings</a></li>
							@if(isset($sprint))
								<li><a id="sprint-settings" href="#" data-toggle="modal" data-target="#sprint-settings-modal">Sprint settings</a></li>
							@endif
						@endif
						<li>{!! link_to_route('logout_path', 'Logout', ['continue' => Request::path()]) !!}</li>
					</ul>
				</li>

				@include('layouts.partials.conduit_api_token_form')
			@else
				{!! link_to(
					env('PHABRICATOR_URL') . 'oauthserver/auth/?' . http_build_query([
						'response_type' => 'code',
						'client_id' => env('OAUTH_CLIENT_ID'),
						'redirect_uri' => route('login_path', ['continue' => Request::path()]),
						'scope' => 'whoami',
					]),
					'Log in using Phabricator',
					['class' => 'btn btn-default navbar-btn btn-sm']
				) !!}
			@endif
		</ul>
	</div>
</nav>

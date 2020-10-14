<html>

<body>

<h2>WAF Security Exception</h2>

<p>Type: <pre>{$exception->type}</pre></p>

<p>Sample: <pre>{$exception->sample}</pre></p>

<p>GET Parameters: <pre>{$exception->get_params}</pre></p>

<p>POST Parameters: <pre>{$exception->post_params}</pre></p>

<p>IP: <pre>{$exception->ip}</pre></p>

<p>User Agent: <pre>{$exception->user_agent}</pre></p>

<p>Browser: <pre>{$exception->browser}</pre></p>

<p>Country: <pre>{$exception->country}</pre></p>

<p>UUID: <pre>{$exception->uuid}</pre></p>

<p>URL: <pre>{$request->getPublicURL()}</pre></p>

<p>Date: <pre>{$exception->created_at|date_format:' %m-%d-%Y %H:%M'}</pre></p>

</body>
</html>
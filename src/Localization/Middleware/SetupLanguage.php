<?php

namespace Volcano\Localization\Middleware;

use Volcano\Foundation\Application;

use Closure;


class SetupLanguage
{

    /**
     * La mise en œuvre de l'application.
     *
     * @var \Volcano\Foundation\Application
     */
    protected $app;


    /**
     * Créez une nouvelle instance de middleware.
     *
     * @param  \Volcano\Foundation\Application  $app
     * @return void
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Traiter une demande entrante.
     *
     * @param  \Volcano\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     *
     * @throws \Volcano\Http\Exception\PostTooLargeException
     */
    public function handle($request, Closure $next)
    {
        $this->updateLocale($request);

        return $next($request);
    }

    /**
     * Mettez à jour les paramètres régionaux de l'application.
     *
     * @param  \Volcano\Http\Request  $request
     * @return void
     */
    protected function updateLocale($request)
    {
        $session = $this->app['session'];

        if ($session->has('language')) {
            $locale = $session->get('language');
        } else {
            $locale = $request->cookie(PREFIX .'language', $this->app['config']['app.locale']);

            $session->set('language', $locale);
        }

        $this->app['language']->setLocale($locale);
    }
}

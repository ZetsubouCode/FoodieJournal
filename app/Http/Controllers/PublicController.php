<?php

namespace App\Http\Controllers;

use App\Models\Place;
use App\Models\SlugRedirect;
use App\Models\Visit;
use App\Services\ExploreQuery;
use App\Services\HomepageDiscovery;
use App\Support\ExploreState;
use App\Support\PublicOptions;
use Illuminate\Http\Request;

class PublicController extends Controller
{
    public function home(PublicOptions $options)
    {
        $visits = Visit::publishedVisible()->withRating()->with($this->cardRelations())
            ->orderByDesc('visited_at')->orderByDesc('id')->limit(10)->get();
        $featured = $visits->first(fn ($visit) => $visit->coverMedia?->variant('hero')) ?? $visits->first();
        $picks = Visit::publishedVisible()->withRating()->whereHas('recommendations')
            ->with($this->cardRelations())->orderByDesc('visited_at')->orderByDesc('id')->limit(10)->get();
        $publicOptions = $options->all();

        return view('public.home', [
            'featured' => $featured, 'visits' => $visits, 'picks' => $picks,
            'discovery' => app(HomepageDiscovery::class)->tiles($publicOptions, $featured),
        ]);
    }

    public function explore(Request $request, ExploreQuery $engine, PublicOptions $optionBuilder)
    {
        $state = new ExploreState($request->query());
        $options = $optionBuilder->all();
        $results = $engine->build($state)->paginate(18, ['*'], 'page', $state->get('page', 1))->appends(collect($state->values)->except('page')->all());

        return view('public.explore', ['state' => $state, 'results' => $results, 'options' => $options, 'chips' => $state->chips($options)]);
    }

    public function count(Request $request, ExploreQuery $engine)
    {
        return response()->json(['total' => $engine->build(new ExploreState($request->query()))->reorder()->count()])->header('Cache-Control', 'no-store');
    }

    public function visit(string $slug)
    {
        $visit = Visit::publishedVisible()->where('slug', $slug)->withRating()->with(['place', 'coverMedia.variants', 'items.foodType.category', 'items.cuisines', 'items.styles', 'items.ingredients', 'items.allergens', 'items.media.variants', 'media.variants', 'tags', 'recommendations'])->first();
        if (! $visit) {
            return $this->redirectSlug('visit', $slug);
        }

        return view('public.visit', ['visit' => $visit, 'preview' => false, 'related' => Visit::publishedVisible()->withRating()->where('place_id', $visit->place_id)->whereKeyNot($visit->id)->with($this->cardRelations())->orderByDesc('visited_at')->orderByDesc('id')->limit(3)->get()]);
    }

    public function place(string $slug)
    {
        $place = Place::publishedVisible()->withRating()->where('slug', $slug)->first();
        if (! $place) {
            return $this->redirectSlug('place', $slug);
        }
        $visits = $place->visits()->publishedVisible()->withRating()->with($this->cardRelations())->orderByDesc('visited_at')->orderByDesc('id')->paginate(18);

        return view('public.place', compact('place', 'visits'));
    }

    private function redirectSlug(string $type, string $old)
    {
        $redirect = SlugRedirect::where('entity_type', $type)->where('old_slug', $old)->where('allow_public_redirect', true)->firstOrFail();
        $class = $type === 'visit' ? Visit::class : Place::class;
        $target = $class::publishedVisible()->findOrFail($redirect->entity_id);

        return redirect('/'.$type.'/'.$target->slug, 301);
    }

    public function sitemap()
    {
        return response()->view('public.sitemap', ['visits' => Visit::publishedVisible()->select('slug', 'updated_at')->get(), 'places' => Place::publishedVisible()->select('slug', 'updated_at')->get()])->header('Content-Type', 'application/xml; charset=UTF-8');
    }

    public function robots()
    {
        return response("User-agent: *\nDisallow: /admin\nDisallow: /media/\nSitemap: ".url('/sitemap.xml')."\n")->header('Content-Type', 'text/plain');
    }

    private function cardRelations(): array
    {
        return ['place', 'coverMedia.variants', 'recommendations', 'tags', 'items.foodType.category', 'items.styles'];
    }
}

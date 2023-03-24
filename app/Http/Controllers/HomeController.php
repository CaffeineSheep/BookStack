<?php

namespace BookStack\Http\Controllers;

use BookStack\Actions\ActivityQueries;
use BookStack\Entities\BasicListItem;
use BookStack\Entities\Models\Book;
use BookStack\Entities\Models\Bookshelf;
use BookStack\Entities\Models\Page;
use BookStack\Uploads\FaviconHandler;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Display the homepage.
     */
    public function index(Request $request, ActivityQueries $activities)
    {
        $activeUsers = $activities->recentlyActiveUsers(3);

        $newSymbols = Page::getVisiblePagesInBookshelf('symbols')->orderBy('created_at', 'desc');
            

        $latestDrafts = Page::getVisiblePagesInBookshelf('contribute')
            ->where('book_id', '=',  Book::getBySlug('drafts', true)->id)    
            ->orderBy('created_at', 'desc')
            ->take(3)
            // ->select(Page::$listAttributes)
            ->get();

        $draftHelp = Page::getVisiblePagesInBookshelf('contribute')
            ->where('book_id', '=',  Book::getBySlug('draft-help', true)->id)
            ->orderBy('created_at', 'desc');

        $communityReviews = Page::getVisiblePagesInBookshelf('contribute')
            ->where('book_id', '=',  Book::getBySlug('community-review', true)->id)
            ->orderBy('created_at', 'desc');


        $quickLinks = collect([
            new BasicListItem('/shelves/symbols/all', 'All Symbols', 'See all of the official symbols', 'star-circle'),
            new BasicListItem(env('TASK_MANAGER_URL', null), 'SymbolHub', 'Find new symbols to add', 'symbolhub'),
            new BasicListItem('/books/general', 'Help', 'Learn how to use Symbolpedia!', 'info'),
            ...Bookshelf::getBySlug('contribute')->visibleBooks()->get()->all(),
        ]);

        $recentUpdates = Page::getVisiblePagesInBookshelf('symbols')
        ->orderBy('updated_at', 'desc')
        ->where('revision_count', '>', 1)
        ->take(3)
        ->select(Page::$listAttributes)
        ->get();

        $symbolTypesList = Bookshelf::getBySlug('symbols', true)->visibleBooks()->get();

        $homepageOptions = ['default', 'books', 'bookshelves', 'page'];
        $homepageOption = setting('app-homepage-type', 'default');
        if (!in_array($homepageOption, $homepageOptions)) {
            $homepageOption = 'default';
        }


        $commonData = [
            'activeUsers' => $activeUsers,
            'latestDrafts' => $latestDrafts,
            'latestCommunityReviews' => $communityReviews->take(6)->get(),
            'numCommunityReviews' => $communityReviews->count(),
            'latestDraftHelp' => $draftHelp->take(6)->get(),
            'numDraftHelp' => $draftHelp->count(),
            'newSymbols' => $newSymbols->take(6)->get(),
            'quickLinks' => $quickLinks,
            'symbolTypesList' => $symbolTypesList,
            'recentUpdates' => $recentUpdates,
        ];

        return view('home.default', $commonData);
    }

    /**
     * Show the view for /robots.txt.
     */
    public function robots()
    {
        $sitePublic = setting('app-public', false);
        $allowRobots = config('app.allow_robots');

        if ($allowRobots === null) {
            $allowRobots = $sitePublic;
        }

        return response()
            ->view('misc.robots', ['allowRobots' => $allowRobots])
            ->header('Content-Type', 'text/plain');
    }

    /**
     * Show the route for 404 responses.
     */
    public function notFound()
    {
        return response()->view('errors.404', [], 404);
    }

    /**
     * Serve the application favicon.
     * Ensures a 'favicon.ico' file exists at the web root location (if writable) to be served
     * directly by the webserver in the future.
     */
    public function favicon(FaviconHandler $favicons)
    {
        $exists = $favicons->restoreOriginalIfNotExists();
        return response()->file($exists ? $favicons->getPath() : $favicons->getOriginalPath());
    }
}

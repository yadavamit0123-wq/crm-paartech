<?php

namespace App\Http\Controllers;

use App\Models\VisitingCard;
use Illuminate\View\View;

class VisitingCardController extends Controller
{
    public function show(string $slug): View
    {
        $card = VisitingCard::where('slug', $slug)->where('is_public', true)->firstOrFail();

        return view('visiting-cards.public', compact('card'));
    }
}

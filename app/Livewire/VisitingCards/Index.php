<?php

namespace App\Livewire\VisitingCards;

use App\Models\VisitingCard;
use Illuminate\Support\Str;
use Livewire\Component;

class Index extends Component
{
    public bool $showModal = false;
    public string $name = '';
    public string $designation = '';
    public string $phone = '';
    public string $email = '';
    public string $website = '';

    public function openCreate(): void
    {
        $user = auth()->user();
        $this->name = $user->name;
        $this->email = $user->email;
        $this->phone = $user->phone ?? '';
        $this->designation = '';
        $this->website = '';
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate(['name' => 'required|string|max:100']);

        $slug = Str::slug($this->name).'-'.Str::random(6);

        VisitingCard::create([
            'tenant_id' => auth()->user()->tenant_id,
            'user_id' => auth()->id(),
            'name' => $this->name,
            'designation' => $this->designation ?: null,
            'phone' => $this->phone ?: null,
            'email' => $this->email ?: null,
            'website' => $this->website ?: null,
            'slug' => $slug,
            'is_public' => true,
        ]);

        $this->showModal = false;
        $this->dispatch('notify', message: 'Visiting card created');
    }

    public function delete(int $id): void
    {
        VisitingCard::where('user_id', auth()->id())->findOrFail($id)->delete();
    }

    public function render()
    {
        $cards = VisitingCard::with('user')->latest()->get();

        return view('livewire.visiting-cards.index', compact('cards'))
            ->layout('layouts.app');
    }
}

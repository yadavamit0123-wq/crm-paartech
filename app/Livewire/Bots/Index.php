<?php

namespace App\Livewire\Bots;

use App\Models\WhatsappBot;
use Livewire\Component;

class Index extends Component
{
    public bool $showModal = false;
    public bool $showTemplates = true;
    public bool $showGuide = false;
    public string $name = '';
    public string $description = '';
    public string $triggerKeyword = '';

    public function openCreate(): void
    {
        $this->reset(['name', 'description', 'triggerKeyword']);
        $this->showModal = true;
    }

    /**
     * Prebuilt bot templates using the same flow_data structure the Builder
     * understands: nodes [id, type, label, text] + edges [from, to].
     * Node types match Builder::$nodeTypes. Each template has a trigger
     * keyword so AutomationService::matchBotReply can match inbound messages.
     */
    public function templates(): array
    {
        return [
            [
                'key' => 'lead_qualification',
                'icon' => '🎯',
                'name' => 'Lead Qualification Bot',
                'desc' => 'Greeting → requirement pucho → budget pucho → team ko route karo. Naye leads ko automatically qualify karta hai.',
                'keyword' => 'hi',
                'nodes' => [
                    ['id' => 'start', 'type' => 'start', 'label' => 'Start'],
                    ['id' => 'node_1', 'type' => 'message', 'label' => 'Greeting', 'text' => 'Namaste! 🙏 Welcome. Main aapki requirement samajhne me help karunga — bas 2 chhote sawal hain.'],
                    ['id' => 'node_2', 'type' => 'text_input', 'label' => 'Ask Requirement', 'text' => 'Aapko kis service/product me interest hai? Short me likh dijiye.'],
                    ['id' => 'node_3', 'type' => 'number_input', 'label' => 'Ask Budget', 'text' => 'Aapka approximate budget kya hai? (sirf number likhiye, e.g. 50000)'],
                    ['id' => 'node_4', 'type' => 'message', 'label' => 'Route to Team', 'text' => 'Perfect, thank you! ✅ Aapki details humari sales team ko forward kar di gayi hain — 30 minute ke andar expert aapko call karega.'],
                ],
            ],
            [
                'key' => 'faq_bot',
                'icon' => '❓',
                'name' => 'FAQ Bot',
                'desc' => 'Common sawalon ke instant jawab — timing, pricing, location. Team ka time bachta hai.',
                'keyword' => 'faq',
                'nodes' => [
                    ['id' => 'start', 'type' => 'start', 'label' => 'Start'],
                    ['id' => 'node_1', 'type' => 'message', 'label' => 'FAQ Menu', 'text' => 'Aapke common sawalon ke jawab: 📌 1) Timing: Mon–Sat, 10am–7pm. 2) Pricing: requirement ke hisaab se — quote ke liye "PRICE" likhiye. 3) Location: office address website par hai. Koi aur sawal? Type kariye, team reply karegi.'],
                    ['id' => 'node_2', 'type' => 'button_question', 'label' => 'More Help', 'text' => 'Kya aapko kisi aur cheez me help chahiye? [Haan — team se baat karo] [Nahi — thank you]'],
                ],
            ],
            [
                'key' => 'appointment_bot',
                'icon' => '📅',
                'name' => 'Appointment Booking Bot',
                'desc' => 'Lead se preferred date/time collect karke booking task create karne me help karta hai.',
                'keyword' => 'appointment',
                'nodes' => [
                    ['id' => 'start', 'type' => 'start', 'label' => 'Start'],
                    ['id' => 'node_1', 'type' => 'message', 'label' => 'Booking Intro', 'text' => 'Appointment book karna bahut easy hai! 📅 Bas apna preferred din aur time bata dijiye.'],
                    ['id' => 'node_2', 'type' => 'text_input', 'label' => 'Ask Date & Time', 'text' => 'Kaunsa din aur time aapke liye best hai? (e.g. "Friday 4pm")'],
                    ['id' => 'node_3', 'type' => 'phone_input', 'label' => 'Confirm Phone', 'text' => 'Confirmation ke liye apna phone number share kar dijiye.'],
                    ['id' => 'node_4', 'type' => 'message', 'label' => 'Confirmation', 'text' => 'Done! ✅ Aapki appointment request receive ho gayi. Humari team confirm karke reminder bhejegi.'],
                ],
            ],
            [
                'key' => 'after_hours',
                'icon' => '🌙',
                'name' => 'After-Hours Auto-Reply Bot',
                'desc' => 'Office band hone ke baad aane wale messages ka polite auto-reply — lead kabhi ignored feel nahi karta.',
                'keyword' => 'hello',
                'nodes' => [
                    ['id' => 'start', 'type' => 'start', 'label' => 'Start'],
                    ['id' => 'node_1', 'type' => 'message', 'label' => 'After-Hours Reply', 'text' => 'Namaste! 🌙 Abhi humara office time nahi hai (working hours: Mon–Sat, 10am–7pm). Aapka message receive ho gaya hai — kal subah sabse pehle humari team aapko reply karegi. Urgent ho toh apna sawal yahin chhod dijiye.'],
                    ['id' => 'node_2', 'type' => 'text_input', 'label' => 'Capture Query', 'text' => 'Apna sawal ya requirement yahan likh dijiye, taaki team ready jawab ke saath contact kare.'],
                ],
            ],
            [
                'key' => 'feedback_bot',
                'icon' => '⭐',
                'name' => 'Feedback Collection Bot',
                'desc' => 'Service ke baad rating + feedback collect karta hai — improvement aur reviews dono ke liye.',
                'keyword' => 'feedback',
                'nodes' => [
                    ['id' => 'start', 'type' => 'start', 'label' => 'Start'],
                    ['id' => 'node_1', 'type' => 'message', 'label' => 'Feedback Ask', 'text' => 'Aapka experience humare liye important hai! ⭐ 1 se 5 me rating dijiye (5 = excellent).'],
                    ['id' => 'node_2', 'type' => 'number_input', 'label' => 'Rating Input', 'text' => 'Apni rating number me bhejiye (1–5).'],
                    ['id' => 'node_3', 'type' => 'text_input', 'label' => 'Detail Feedback', 'text' => 'Thank you! Kya aap 1 line me batayenge hum kya improve kar sakte hain?'],
                    ['id' => 'node_4', 'type' => 'message', 'label' => 'Thank You', 'text' => 'Shukriya! 🙏 Aapka feedback team tak pahunch gaya. Agar experience accha laga toh Google par review zaroor dijiye.'],
                ],
            ],
            [
                'key' => 'order_status',
                'icon' => '📦',
                'name' => 'Order Status Bot',
                'desc' => '"Order" likhne par status-check flow — order ID collect karke team ko update task deta hai.',
                'keyword' => 'order',
                'nodes' => [
                    ['id' => 'start', 'type' => 'start', 'label' => 'Start'],
                    ['id' => 'node_1', 'type' => 'message', 'label' => 'Order Help', 'text' => 'Order status check karna hai? 📦 No problem — bas apna Order ID bhej dijiye.'],
                    ['id' => 'node_2', 'type' => 'text_input', 'label' => 'Ask Order ID', 'text' => 'Apna Order ID ya booking reference number likhiye.'],
                    ['id' => 'node_3', 'type' => 'message', 'label' => 'Status Reply', 'text' => 'Thank you! Humari team aapke order ka latest status check karke kuch hi minutes me update bhejegi. ✅'],
                ],
            ],
            [
                'key' => 'reengagement_bot',
                'icon' => '🔄',
                'name' => 'Re-Engagement Bot',
                'desc' => 'Purane/cold leads ko offer ke saath wapas active karta hai — "OFFER" reply par deal details.',
                'keyword' => 'offer',
                'nodes' => [
                    ['id' => 'start', 'type' => 'start', 'label' => 'Start'],
                    ['id' => 'node_1', 'type' => 'message', 'label' => 'Offer Reveal', 'text' => 'Great choice! 🎁 Is week ke liye special offer: early booking par extra discount + priority service. Limited slots hain!'],
                    ['id' => 'node_2', 'type' => 'button_question', 'label' => 'Claim Offer', 'text' => 'Offer claim karna chahenge? [Haan — abhi book karo] [Details chahiye]'],
                    ['id' => 'node_3', 'type' => 'message', 'label' => 'Team Handoff', 'text' => 'Awesome! Humari team aapko call karke offer activate kar degi. 🚀'],
                ],
            ],
            [
                'key' => 'pricing_bot',
                'icon' => '💰',
                'name' => 'Pricing Enquiry Bot',
                'desc' => '"Price" puchne wale leads ko instant pricing info + quote request flow.',
                'keyword' => 'price',
                'nodes' => [
                    ['id' => 'start', 'type' => 'start', 'label' => 'Start'],
                    ['id' => 'node_1', 'type' => 'message', 'label' => 'Pricing Info', 'text' => 'Pricing aapki exact requirement par depend karti hai 💰 — lekin ek accurate quote ke liye bas 1 detail chahiye.'],
                    ['id' => 'node_2', 'type' => 'text_input', 'label' => 'Ask Requirement', 'text' => 'Aapko kaunsi service/quantity chahiye? Short me likhiye — hum turant quote banayenge.'],
                    ['id' => 'node_3', 'type' => 'message', 'label' => 'Quote Promise', 'text' => 'Perfect! ✅ Aapka customized quote 15 minute me WhatsApp par mil jayega.'],
                ],
            ],
        ];
    }

    public function installTemplate(string $key): void
    {
        $template = collect($this->templates())->firstWhere('key', $key);
        if (! $template) {
            return;
        }

        if (WhatsappBot::where('name', $template['name'])->exists()) {
            $this->dispatch('notify', message: 'Already installed', type: 'error');

            return;
        }

        $nodes = $template['nodes'];
        $edges = [];
        for ($i = 1; $i < count($nodes); $i++) {
            $edges[] = ['from' => $nodes[$i - 1]['id'], 'to' => $nodes[$i]['id']];
        }

        WhatsappBot::create([
            'tenant_id' => auth()->user()->tenant_id,
            'name' => $template['name'],
            'description' => $template['desc'],
            'trigger_keyword' => $template['keyword'],
            'flow_data' => [
                'nodes' => $nodes,
                'edges' => $edges,
                'trigger_type' => 'keyword',
            ],
        ]);

        $this->dispatch('notify', message: $template['name'].' installed — open in Builder to customise');
    }

    public function save(): void
    {
        $this->validate(['name' => 'required|string|max:100']);

        $bot = WhatsappBot::create([
            'tenant_id' => auth()->user()->tenant_id,
            'name' => $this->name,
            'description' => $this->description ?: null,
            'trigger_keyword' => $this->triggerKeyword ?: null,
            'flow_data' => [
                'nodes' => [
                    ['id' => 'start', 'type' => 'start', 'label' => 'Start'],
                    ['id' => 'welcome', 'type' => 'message', 'label' => 'Welcome Message', 'text' => 'Hi! How can we help you?'],
                ],
                'edges' => [['from' => 'start', 'to' => 'welcome']],
            ],
        ]);

        $this->showModal = false;
        $this->redirect(route('leads.bots.builder', $bot), navigate: true);
    }

    public function toggleActive(int $id): void
    {
        $bot = WhatsappBot::findOrFail($id);
        $bot->update(['is_active' => ! $bot->is_active]);
    }

    public function delete(int $id): void
    {
        WhatsappBot::findOrFail($id)->delete();
        $this->dispatch('notify', message: 'Bot deleted');
    }

    public function render()
    {
        $bots = WhatsappBot::latest()->get();
        $templates = $this->templates();
        $installed = $bots->keyBy('name');

        return view('livewire.bots.index', compact('bots', 'templates', 'installed'))
            ->layout('layouts.app');
    }
}

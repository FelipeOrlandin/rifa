# Rifa Online Platform Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use compose:subagent (recommended) or compose:execute to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Build a complete online raffle sales platform with Livewire reactivity, glassmorphism UI, and admin management on shared hosting (Laravel + MySQL).

**Architecture:** Laravel 10 with Livewire for real-time interactions, Alpine.js for micro-interactions, TailwindCSS for styling. Database transactions with `lockForUpdate()` for concurrency control. File-based caching via `Cache::remember()` for shared hosting compatibility.

**Tech Stack:** Laravel 10, Livewire 3, Alpine.js, TailwindCSS 3, MySQL 8, PHP 8.1+

---

## File Structure

```
app/
├── Http/
│   └── Controllers/
│       ├── RifaController.php          # Public rifa listing & detail
│       └── Admin/
│           ├── DashboardController.php
│           ├── RifaController.php      # Admin CRUD
│           └── SorteioController.php   # Draw system
├── Livewire/
│   ├── RifaGrid.php                    # Number grid component
│   ├── CartSummary.php                 # Dynamic cart sidebar
│   └── Admin/
│       ├── RifaForm.php                # Create/Edit form
│       └── NumberManager.php           # Manage numbers status
├── Models/
│   ├── Rifa.php
│   ├── RifaNumber.php
│   ├── Payment.php
│   └── Winner.php
└── Services/
    └── RifaService.php                 # Business logic (concurrency, cache)

database/migrations/
├── 2024_01_01_000001_create_rifas_table.php
├── 2024_01_01_000002_create_rifa_numbers_table.php
├── 2024_01_01_000003_create_payments_table.php
└── 2024_01_01_000004_create_winners_table.php

resources/views/
├── livewire/
│   ├── rifa-grid.blade.php
│   └── cart-summary.blade.php
├── rifa/
│   ├── index.blade.php
│   └── show.blade.php
└── admin/
    ├── dashboard.blade.php
    ├── rifas/
    │   ├── index.blade.php
    │   ├── create.blade.php
    │   ├── edit.blade.php
    │   └── numbers.blade.php
    └── sorteio/
        └── index.blade.php
```

---

## Task 1: Database Migrations

**Covers:** Database schema, relationships, indexes for concurrency control

**Files:**
- Create: `database/migrations/2024_01_01_000001_create_rifas_table.php`
- Create: `database/migrations/2024_01_01_000002_create_rifa_numbers_table.php`
- Create: `database/migrations/2024_01_01_000003_create_payments_table.php`
- Create: `database/migrations/2024_01_01_000004_create_winners_table.php`

- [ ] **Step 1: Create Rifas table migration**

```php
<?php
// database/migrations/2024_01_01_000001_create_rifas_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rifas', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('prize_image')->nullable();
            $table->decimal('number_price', 8, 2);
            $table->integer('total_numbers');
            $table->decimal('prize_value', 10, 2)->nullable();
            $table->enum('status', ['active', 'closed', 'drawn'])->default('active');
            $table->timestamp('draw_date');
            $table->timestamps();
            
            $table->index('status');
            $table->index('slug');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rifas');
    }
};
```

- [ ] **Step 2: Create Rifa Numbers table migration**

```php
<?php
// database/migrations/2024_01_01_000002_create_rifa_numbers_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rifa_numbers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rifa_id')->constrained()->onDelete('cascade');
            $table->integer('number');
            $table->enum('status', ['available', 'reserved', 'paid'])->default('available');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->timestamp('reserved_at')->nullable();
            $table->timestamps();
            
            $table->unique(['rifa_id', 'number']);
            $table->index(['rifa_id', 'status']);
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rifa_numbers');
    }
};
```

- [ ] **Step 3: Create Payments table migration**

```php
<?php
// database/migrations/2024_01_01_000003_create_payments_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('rifa_id')->constrained()->onDelete('cascade');
            $table->decimal('amount', 10, 2);
            $table->enum('status', ['pending', 'completed', 'failed', 'refunded'])->default('pending');
            $table->string('payment_method')->nullable();
            $table->string('transaction_id')->nullable();
            $table->json('numbers_purchased')->nullable();
            $table->timestamps();
            
            $table->index('user_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
```

- [ ] **Step 4: Create Winners table migration**

```php
<?php
// database/migrations/2024_01_01_000004_create_winners_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('winners', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rifa_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('rifa_number_id')->constrained('rifa_numbers')->onDelete('cascade');
            $table->integer('winning_number');
            $table->boolean('is_manual')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->unique('rifa_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('winners');
    }
};
```

- [ ] **Step 5: Run migrations**

Run: `php artisan migrate`
Expected: All 4 tables created successfully

- [ ] **Step 6: Commit**

```bash
git add database/migrations/
git commit -m "feat: add database migrations for rifa platform"
```

---

## Task 2: Models & Relationships

**Covers:** Eloquent models with relationships, scopes, and accessors

**Files:**
- Create: `app/Models/Rifa.php`
- Create: `app/Models/RifaNumber.php`
- Create: `app/Models/Payment.php`
- Create: `app/Models/Winner.php`

- [ ] **Step 1: Create Rifa model**

```php
<?php
// app/Models/Rifa.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class Rifa extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'description',
        'prize_image',
        'number_price',
        'total_numbers',
        'prize_value',
        'status',
        'draw_date',
    ];

    protected $casts = [
        'number_price' => 'decimal:2',
        'prize_value' => 'decimal:2',
        'total_numbers' => 'integer',
        'draw_date' => 'datetime',
    ];

    protected static function boot(): void
    {
        parent::boot();
        
        static::creating(function ($rifa) {
            if (empty($rifa->slug)) {
                $rifa->slug = Str::slug($rifa->title);
            }
        });
    }

    public function numbers(): HasMany
    {
        return $this->hasMany(RifaNumber::class);
    }

    public function winner(): HasOne
    {
        return $this->hasOne(Winner::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function getAvailableNumbersCountAttribute(): int
    {
        return $this->numbers()->where('status', 'available')->count();
    }

    public function getSoldNumbersCountAttribute(): int
    {
        return $this->numbers()->where('status', 'paid')->count();
    }

    public function getProgressPercentageAttribute(): float
    {
        return $this->total_numbers > 0 
            ? ($this->sold_numbers_count / $this->total_numbers) * 100 
            : 0;
    }
}
```

- [ ] **Step 2: Create RifaNumber model**

```php
<?php
// app/Models/RifaNumber.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RifaNumber extends Model
{
    use HasFactory;

    protected $fillable = [
        'rifa_id',
        'number',
        'status',
        'user_id',
        'reserved_at',
    ];

    protected $casts = [
        'number' => 'integer',
        'reserved_at' => 'datetime',
    ];

    public function rifa(): BelongsTo
    {
        return $this->belongsTo(Rifa::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeAvailable($query)
    {
        return $query->where('status', 'available');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }
}
```

- [ ] **Step 3: Create Payment model**

```php
<?php
// app/Models/Payment.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'rifa_id',
        'amount',
        'status',
        'payment_method',
        'transaction_id',
        'numbers_purchased',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'numbers_purchased' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function rifa(): BelongsTo
    {
        return $this->belongsTo(Rifa::class);
    }
}
```

- [ ] **Step 4: Create Winner model**

```php
<?php
// app/Models/Winner.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Winner extends Model
{
    use HasFactory;

    protected $fillable = [
        'rifa_id',
        'user_id',
        'rifa_number_id',
        'winning_number',
        'is_manual',
        'notes',
    ];

    protected $casts = [
        'winning_number' => 'integer',
        'is_manual' => 'boolean',
    ];

    public function rifa(): BelongsTo
    {
        return $this->belongsTo(Rifa::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function rifaNumber(): BelongsTo
    {
        return $this->belongsTo(RifaNumber::class);
    }
}
```

- [ ] **Step 5: Add User relationship (modify existing User model)**

```php
// Add to app/Models/User.php (inside the class)

use App\Models\RifaNumber;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Relations\HasMany;

// Add these methods:
public function rifaNumbers(): HasMany
{
    return $this->hasMany(RifaNumber::class);
}

public function payments(): HasMany
{
    return $this->hasMany(Payment::class);
}
```

- [ ] **Step 6: Commit**

```bash
git add app/Models/
git commit -m "feat: add Eloquent models with relationships"
```

---

## Task 3: RifaService (Business Logic)

**Covers:** Concurrency control with lockForUpdate(), caching strategy, number reservation logic

**Files:**
- Create: `app/Services/RifaService.php`

- [ ] **Step 1: Create RifaService**

```php
<?php
// app/Services/RifaService.php

namespace App\Services;

use App\Models\Rifa;
use App\Models\RifaNumber;
use App\Models\Winner;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Exception;

class RifaService
{
    /**
     * Get rifa with caching for shared hosting
     */
    public function getRifaBySlug(string $slug): ?Rifa
    {
        return Cache::remember("rifa_{$slug}", 300, function () use ($slug) {
            return Rifa::where('slug', $slug)
                ->where('status', 'active')
                ->first();
        });
    }

    /**
     * Get available numbers with caching
     */
    public function getAvailableNumbers(Rifa $rifa): array
    {
        $cacheKey = "rifa_{$rifa->id}_available_numbers";
        
        return Cache::remember($cacheKey, 60, function () use ($rifa) {
            return $rifa->numbers()
                ->where('status', 'available')
                ->pluck('number')
                ->toArray();
        });
    }

    /**
     * Reserve numbers with pessimistic locking (lockForUpdate)
     * This prevents race conditions on shared hosting
     */
    public function reserveNumbers(Rifa $rifa, array $numbers, int $userId): array
    {
        return DB::transaction(function () use ($rifa, $numbers, $userId) {
            $reserved = [];
            $failed = [];
            
            foreach ($numbers as $number) {
                // Lock the row for update to prevent concurrent access
                $rifaNumber = RifaNumber::where('rifa_id', $rifa->id)
                    ->where('number', $number)
                    ->lockForUpdate()
                    ->first();
                
                if (!$rifaNumber) {
                    $failed[] = $number;
                    continue;
                }
                
                if ($rifaNumber->status !== 'available') {
                    $failed[] = $number;
                    continue;
                }
                
                // Reserve the number
                $rifaNumber->update([
                    'status' => 'reserved',
                    'user_id' => $userId,
                    'reserved_at' => now(),
                ]);
                
                $reserved[] = $number;
            }
            
            // Clear cache after reservation
            $this->clearRifaCache($rifa);
            
            return [
                'reserved' => $reserved,
                'failed' => $failed,
                'total' => count($reserved),
            ];
        });
    }

    /**
     * Confirm payment and mark numbers as paid
     */
    public function confirmPayment(Rifa $rifa, array $numbers, int $userId, string $paymentMethod): bool
    {
        return DB::transaction(function () use ($rifa, $numbers, $userId, $paymentMethod) {
            // Mark numbers as paid
            RifaNumber::where('rifa_id', $rifa->id)
                ->whereIn('number', $numbers)
                ->where('user_id', $userId)
                ->where('status', 'reserved')
                ->update(['status' => 'paid']);
            
            // Create payment record
            $totalAmount = count($numbers) * $rifa->number_price;
            
            $rifa->payments()->create([
                'user_id' => $userId,
                'amount' => $totalAmount,
                'status' => 'completed',
                'payment_method' => $paymentMethod,
                'numbers_purchased' => $numbers,
            ]);
            
            // Clear cache
            $this->clearRifaCache($rifa);
            
            return true;
        });
    }

    /**
     * Draw a winner using database randomization
     */
    public function drawWinner(Rifa $rifa): ?Winner
    {
        if ($rifa->status !== 'closed') {
            throw new Exception('Rifa must be closed before drawing');
        }
        
        return DB::transaction(function () use ($rifa) {
            // Get a random paid number
            $winningNumber = RifaNumber::where('rifa_id', $rifa->id)
                ->where('status', 'paid')
                ->orderByRaw('RAND()')
                ->first();
            
            if (!$winningNumber) {
                throw new Exception('No paid numbers available for drawing');
            }
            
            // Create winner record
            $winner = Winner::create([
                'rifa_id' => $rifa->id,
                'user_id' => $winningNumber->user_id,
                'rifa_number_id' => $winningNumber->id,
                'winning_number' => $winningNumber->number,
                'is_manual' => false,
            ]);
            
            // Update rifa status
            $rifa->update(['status' => 'drawn']);
            
            // Clear all cache for this rifa
            $this->clearRifaCache($rifa);
            
            return $winner;
        });
    }

    /**
     * Manually set a winner (admin override)
     */
    public function setManualWinner(Rifa $rifa, int $number, ?string $notes = null): Winner
    {
        return DB::transaction(function () use ($rifa, $number, $notes) {
            $rifaNumber = RifaNumber::where('rifa_id', $rifa->id)
                ->where('number', $number)
                ->where('status', 'paid')
                ->first();
            
            if (!$rifaNumber) {
                throw new Exception("Number {$number} is not paid or does not exist");
            }
            
            $winner = Winner::create([
                'rifa_id' => $rifa->id,
                'user_id' => $rifaNumber->user_id,
                'rifa_number_id' => $rifaNumber->id,
                'winning_number' => $number,
                'is_manual' => true,
                'notes' => $notes,
            ]);
            
            $rifa->update(['status' => 'drawn']);
            
            $this->clearRifaCache($rifa);
            
            return $winner;
        });
    }

    /**
     * Clear all cache related to a rifa
     */
    protected function clearRifaCache(Rifa $rifa): void
    {
        Cache::forget("rifa_{$rifa->slug}");
        Cache::forget("rifa_{$rifa->id}_available_numbers");
        Cache::forget("rifas_active");
    }
}
```

- [ ] **Step 2: Register service in ServiceProvider**

```php
// Add to app/Providers/AppServiceProvider.php in register() method

use App\Services\RifaService;

$this->app->singleton(RifaService::class, function ($app) {
    return new RifaService();
});
```

- [ ] **Step 3: Commit**

```bash
git add app/Services/ app/Providers/AppServiceProvider.php
git commit -m "feat: add RifaService with concurrency control and caching"
```

---

## Task 4: RifaGrid Livewire Component

**Cages:** Dynamic number grid with glassmorphism UI, selection state, cart integration

**Files:**
- Create: `app/Livewire/RifaGrid.php`
- Create: `resources/views/livewire/rifa-grid.blade.php`

- [ ] **Step 1: Create RifaGrid Livewire component**

```php
<?php
// app/Livewire/RifaGrid.php

namespace App\Livewire;

use App\Models\Rifa;
use App\Services\RifaService;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class RifaGrid extends Component
{
    public Rifa $rifa;
    public array $selectedNumbers = [];
    public array $availableNumbers = [];
    public array $soldNumbers = [];
    public array $reservedNumbers = [];
    
    protected RifaService $rifaService;

    public function boot(RifaService $rifaService): void
    {
        $this->rifaService = $rifaService;
    }

    public function mount(Rifa $rifa): void
    {
        $this->rifa = $rifa;
        $this->loadNumbers();
    }

    public function loadNumbers(): void
    {
        $allNumbers = $this->rifa->numbers()->pluck('number', 'status')->toArray();
        
        $this->availableNumbers = $allNumbers['available'] ?? [];
        $this->soldNumbers = $allNumbers['paid'] ?? [];
        $this->reservedNumbers = $allNumbers['reserved'] ?? [];
    }

    public function toggleNumber(int $number): void
    {
        if (!in_array($number, $this->availableNumbers)) {
            return;
        }

        if (in_array($number, $this->selectedNumbers)) {
            $this->selectedNumbers = array_diff($this->selectedNumbers, [$number]);
        } else {
            $this->selectedNumbers[] = $number;
        }
        
        $this->selectedNumbers = array_values($this->selectedNumbers);
        
        // Dispatch event for cart summary update
        $this->dispatch('numbersUpdated', [
            'selected' => $this->selectedNumbers,
            'total' => count($this->selectedNumbers) * $this->rifa->number_price,
        ]);
    }

    public function reserveSelected(): void
    {
        if (!Auth::check()) {
            $this->dispatch('showAuthModal');
            return;
        }

        if (empty($this->selectedNumbers)) {
            return;
        }

        try {
            $result = $this->rifaService->reserveNumbers(
                $this->rifa,
                $this->selectedNumbers,
                Auth::id()
            );

            if (!empty($result['failed'])) {
                $this->dispatch('showError', [
                    'message' => 'Alguns números não puderam ser reservados: ' . implode(', ', $result['failed']),
                ]);
            }

            // Clear selection and reload
            $this->selectedNumbers = [];
            $this->loadNumbers();
            
            $this->dispatch('reservationComplete');
            
        } catch (\Exception $e) {
            $this->dispatch('showError', [
                'message' => 'Erro ao reservar números: ' . $e->getMessage(),
            ]);
        }
    }

    public function render()
    {
        return view('livewire.rifa-grid');
    }
}
```

- [ ] **Step 2: Create RifaGrid Blade view with glassmorphism**

```blade
{{-- resources/views/livewire/rifa-grid.blade.php --}}

<div class="w-full">
    {{-- Grid Container --}}
    <div class="grid grid-cols-5 sm:grid-cols-8 md:grid-cols-10 lg:grid-cols-12 gap-2 p-4">
        @foreach(range(1, $rifa->total_numbers) as $number)
            @php
                $isAvailable = in_array($number, $availableNumbers);
                $isSold = in_array($number, $soldNumbers);
                $isSelected = in_array($number, $selectedNumbers);
                $isReserved = in_array($number, $reservedNumbers);
            @endphp

            {{-- Number Button --}}
            <button
                wire:click="toggleNumber({{ $number }})"
                @disabled(!$isAvailable)
                class="
                    relative aspect-square rounded-xl font-semibold text-sm
                    transition-all duration-200 ease-out
                    {{ $isAvailable ? 'cursor-pointer hover:scale-105' : 'cursor-not-allowed' }}
                    {{ $isSelected 
                        ? 'bg-gradient-to-br from-emerald-400 to-emerald-600 text-white shadow-lg shadow-emerald-500/30 ring-2 ring-emerald-300' 
                        : ($isSold 
                            ? 'bg-gray-200/50 text-gray-400 opacity-40' 
                            : ($isReserved 
                                ? 'bg-amber-100/50 text-amber-600 opacity-60' 
                                : 'bg-white/20 text-gray-700 hover:bg-white/40 hover:shadow-lg hover:shadow-white/20'
                            )
                        )
                    }}
                    backdrop-blur-sm border border-white/20
                "
                x-data
                @mouseenter="$el.style.transform = '{{ $isAvailable ? 'scale(1.1)' : 'scale(1)' }}'"
                @mouseleave="$el.style.transform = 'scale(1)'"
            >
                {{-- Glass effect overlay for available numbers --}}
                @if($isAvailable && !$isSelected)
                    <div class="absolute inset-0 rounded-xl bg-gradient-to-br from-white/30 to-transparent pointer-events-none"></div>
                @endif
                
                {{-- Number text --}}
                <span class="relative z-10">{{ str_pad($number, 3, '0', STR_PAD_LEFT) }}</span>
                
                {{-- Sold badge --}}
                @if($isSold)
                    <div class="absolute inset-0 flex items-center justify-center">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                @endif
                
                {{-- Selected checkmark --}}
                @if($isSelected)
                    <div class="absolute -top-1 -right-1 w-5 h-5 bg-white rounded-full flex items-center justify-center shadow-md">
                        <svg class="w-3 h-3 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                @endif
            </button>
        @endforeach
    </div>

    {{-- Legend --}}
    <div class="flex flex-wrap gap-4 mt-4 px-4 text-sm">
        <div class="flex items-center gap-2">
            <div class="w-4 h-4 rounded bg-white/20 backdrop-blur-sm border border-white/20"></div>
            <span class="text-gray-600">Disponível</span>
        </div>
        <div class="flex items-center gap-2">
            <div class="w-4 h-4 rounded bg-emerald-500"></div>
            <span class="text-gray-600">Selecionado</span>
        </div>
        <div class="flex items-center gap-2">
            <div class="w-4 h-4 rounded bg-gray-200/50 opacity-40"></div>
            <span class="text-gray-600">Vendido</span>
        </div>
        <div class="flex items-center gap-2">
            <div class="w-4 h-4 rounded bg-amber-100/50 opacity-60"></div>
            <span class="text-gray-600">Reservado</span>
        </div>
    </div>
</div>
```

- [ ] **Step 3: Commit**

```bash
git add app/Livewire/RifaGrid.php resources/views/livewire/rifa-grid.blade.php
git commit -m "feat: add RifaGrid Livewire component with glassmorphism UI"
```

---

## Task 5: CartSummary Livewire Component

**Covers:** Dynamic cart summary, payment initiation

**Files:**
- Create: `app/Livewire/CartSummary.php`
- Create: `resources/views/livewire/cart-summary.blade.php`

- [ ] **Step 1: Create CartSummary component**

```php
<?php
// app/Livewire/CartSummary.php

namespace App\Livewire;

use App\Models\Rifa;
use Livewire\Component;

class CartSummary extends Component
{
    public Rifa $rifa;
    public array $selectedNumbers = [];
    public float $total = 0;

    protected $listeners = ['numbersUpdated' => 'updateSelection'];

    public function mount(Rifa $rifa): void
    {
        $this->rifa = $rifa;
    }

    public function updateSelection(array $data): void
    {
        $this->selectedNumbers = $data['selected'];
        $this->total = $data['total'];
    }

    public function render()
    {
        return view('livewire.cart-summary');
    }
}
```

- [ ] **Step 2: Create CartSummary Blade view**

```blade
{{-- resources/views/livewire/cart-summary.blade.php --}}

<div 
    x-data 
    x-show="{{ count($selectedNumbers) > 0 }}"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0 translate-y-4"
    x-transition:enter-end="opacity-100 translate-y-0"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100 translate-y-0"
    x-transition:leave-end="opacity-0 translate-y-4"
    class="fixed bottom-0 left-0 right-0 z-50"
>
    {{-- Glassmorphism background --}}
    <div class="bg-white/80 backdrop-blur-lg border-t border-white/20 shadow-2xl">
        <div class="max-w-7xl mx-auto px-4 py-4">
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                {{-- Selection info --}}
                <div class="flex items-center gap-4">
                    {{-- Selected numbers preview --}}
                    <div class="flex -space-x-2">
                        @foreach(array_slice($selectedNumbers, 0, 5) as $number)
                            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-emerald-400 to-emerald-600 flex items-center justify-center text-white text-xs font-bold border-2 border-white shadow-sm">
                                {{ str_pad($number, 3, '0', STR_PAD_LEFT) }}
                            </div>
                        @endforeach
                        @if(count($selectedNumbers) > 5)
                            <div class="w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center text-gray-600 text-xs font-bold border-2 border-white shadow-sm">
                                +{{ count($selectedNumbers) - 5 }}
                            </div>
                        @endif
                    </div>

                    {{-- Summary text --}}
                    <div class="text-gray-800">
                        <span class="font-bold text-lg">{{ count($selectedNumbers) }}</span>
                        <span class="text-gray-600">
                            {{ Str::plural('número', count($selectedNumbers)) }} selecionado{{ count($selectedNumbers) > 1 ? 's' : '' }}
                        </span>
                        <span class="mx-2 text-gray-400">|</span>
                        <span class="font-bold text-xl text-emerald-600">
                            R$ {{ number_format($total, 2, ',', '.') }}
                        </span>
                    </div>
                </div>

                {{-- Action buttons --}}
                <div class="flex items-center gap-3">
                    <button
                        wire:click="$parent.reserveSelected"
                        @disabled="count($selectedNumbers) === 0"
                        class="
                            px-6 py-3 rounded-xl font-semibold text-white
                            bg-gradient-to-r from-emerald-500 to-emerald-600
                            hover:from-emerald-600 hover:to-emerald-700
                            transform hover:scale-105 transition-all duration-200
                            shadow-lg shadow-emerald-500/30
                            disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none
                            flex items-center gap-2
                        "
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                        Reservar e Pagar
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
```

- [ ] **Step 3: Commit**

```bash
git add app/Livewire/CartSummary.php resources/views/livewire/cart-summary.blade.php
git commit -m "feat: add CartSummary Livewire component with glassmorphism"
```

---

## Task 6: Public Rifa Pages

**Covers:** Rifa listing and detail pages with full integration

**Files:**
- Create: `app/Http/Controllers/RifaController.php`
- Create: `resources/views/rifa/index.blade.php`
- Create: `resources/views/rifa/show.blade.php`

- [ ] **Step 1: Create RifaController**

```php
<?php
// app/Http/Controllers/RifaController.php

namespace App\Http\Controllers;

use App\Models\Rifa;
use App\Services\RifaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class RifaController extends Controller
{
    public function index()
    {
        $rifas = Cache::remember('rifas_active', 300, function () {
            return Rifa::active()
                ->withCount(['numbers as available_count' => function ($q) {
                    $q->where('status', 'available');
                }])
                ->withCount(['numbers as sold_count' => function ($q) {
                    $q->where('status', 'paid');
                }])
                ->orderBy('draw_date', 'asc')
                ->get();
        });

        return view('rifa.index', compact('rifas'));
    }

    public function show(string $slug, RifaService $rifaService)
    {
        $rifa = $rifaService->getRifaBySlug($slug);
        
        if (!$rifa) {
            abort(404);
        }

        return view('rifa.show', compact('rifa'));
    }
}
```

- [ ] **Step 2: Create index.blade.php (listing page)**

```blade
{{-- resources/views/rifa/index.blade.php --}}

<x-app-layout>
    <div class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100">
        {{-- Header --}}
        <div class="bg-white/60 backdrop-blur-sm border-b border-white/20">
            <div class="max-w-7xl mx-auto px-4 py-8">
                <h1 class="text-4xl font-bold text-gray-800">Rifas Disponíveis</h1>
                <p class="text-gray-600 mt-2">Escolha sua rifa e concorra a prêmios incríveis!</p>
            </div>
        </div>

        {{-- Rifa Cards --}}
        <div class="max-w-7xl mx-auto px-4 py-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse($rifas as $rifa)
                    <a 
                        href="{{ route('rifa.show', $rifa->slug) }}"
                        class="group block bg-white/40 backdrop-blur-sm rounded-2xl border border-white/20 shadow-lg hover:shadow-2xl transition-all duration-300 hover:scale-[1.02] overflow-hidden"
                    >
                        {{-- Image --}}
                        <div class="aspect-video bg-gradient-to-br from-emerald-400 to-emerald-600 relative overflow-hidden">
                            @if($rifa->prize_image)
                                <img 
                                    src="{{ asset('storage/' . $rifa->prize_image) }}" 
                                    alt="{{ $rifa->title }}"
                                    class="w-full h-full object-cover"
                                >
                            @else
                                <div class="w-full h-full flex items-center justify-center">
                                    <svg class="w-16 h-16 text-white/50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7"></path>
                                    </svg>
                                </div>
                            @endif
                            
                            {{-- Status badge --}}
                            <div class="absolute top-3 right-3">
                                <span class="px-3 py-1 bg-white/90 backdrop-blur-sm rounded-full text-xs font-semibold text-emerald-600">
                                    Ativa
                                </span>
                            </div>
                        </div>

                        {{-- Content --}}
                        <div class="p-5">
                            <h3 class="text-xl font-bold text-gray-800 group-hover:text-emerald-600 transition-colors">
                                {{ $rifa->title }}
                            </h3>
                            
                            <p class="text-gray-600 text-sm mt-2 line-clamp-2">
                                {{ $rifa->description }}
                            </p>

                            {{-- Stats --}}
                            <div class="mt-4 flex items-center justify-between text-sm">
                                <div class="text-gray-500">
                                    <span class="font-semibold text-emerald-600">R$ {{ number_format($rifa->number_price, 2, ',', '.') }}</span>
                                    por número
                                </div>
                                <div class="text-gray-500">
                                    {{ $rifa->available_count }} disponíveis
                                </div>
                            </div>

                            {{-- Progress bar --}}
                            <div class="mt-4">
                                <div class="h-2 bg-gray-200 rounded-full overflow-hidden">
                                    <div 
                                        class="h-full bg-gradient-to-r from-emerald-400 to-emerald-600 transition-all duration-500"
                                        style="width: {{ $rifa->total_numbers > 0 ? (($rifa->sold_count / $rifa->total_numbers) * 100) : 0 }}%"
                                    ></div>
                                </div>
                                <div class="flex justify-between mt-1 text-xs text-gray-500">
                                    <span>{{ $rifa->sold_count }} vendidos</span>
                                    <span>{{ $rifa->total_numbers }} total</span>
                                </div>
                            </div>

                            {{-- Draw date --}}
                            <div class="mt-4 flex items-center gap-2 text-sm text-gray-500">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                Sorteio: {{ $rifa->draw_date->format('d/m/Y') }}
                            </div>
                        </div>
                    </a>
                @empty
                    <div class="col-span-full text-center py-16">
                        <svg class="w-16 h-16 text-gray-300 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                        </svg>
                        <h3 class="mt-4 text-lg font-medium text-gray-900">Nenhuma rifa disponível</h3>
                        <p class="mt-2 text-gray-500">Volte em breve para conferir novas rifas!</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
```

- [ ] **Step 3: Create show.blade.php (detail page)**

```blade
{{-- resources/views/rifa/show.blade.php --}}

<x-app-layout>
    <div class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100">
        {{-- Header --}}
        <div class="bg-white/60 backdrop-blur-sm border-b border-white/20">
            <div class="max-w-7xl mx-auto px-4 py-6">
                <a href="{{ route('rifas') }}" class="inline-flex items-center text-gray-600 hover:text-emerald-600 transition-colors mb-4">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Voltar
                </a>
                
                <div class="flex flex-col lg:flex-row gap-8">
                    {{-- Prize Image --}}
                    <div class="lg:w-1/3">
                        <div class="aspect-square bg-gradient-to-br from-emerald-400 to-emerald-600 rounded-2xl overflow-hidden shadow-xl">
                            @if($rifa->prize_image)
                                <img 
                                    src="{{ asset('storage/' . $rifa->prize_image) }}" 
                                    alt="{{ $rifa->title }}"
                                    class="w-full h-full object-cover"
                                >
                            @else
                                <div class="w-full h-full flex items-center justify-center">
                                    <svg class="w-24 h-24 text-white/50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7"></path>
                                    </svg>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Rifa Info --}}
                    <div class="lg:w-2/3">
                        <h1 class="text-3xl font-bold text-gray-800">{{ $rifa->title }}</h1>
                        
                        <p class="text-gray-600 mt-4 leading-relaxed">{{ $rifa->description }}</p>

                        <div class="mt-6 grid grid-cols-2 gap-4">
                            <div class="bg-white/40 backdrop-blur-sm rounded-xl p-4 border border-white/20">
                                <div class="text-sm text-gray-500">Valor por número</div>
                                <div class="text-2xl font-bold text-emerald-600">R$ {{ number_format($rifa->number_price, 2, ',', '.') }}</div>
                            </div>
                            <div class="bg-white/40 backdrop-blur-sm rounded-xl p-4 border border-white/20">
                                <div class="text-sm text-gray-500">Data do sorteio</div>
                                <div class="text-2xl font-bold text-gray-800">{{ $rifa->draw_date->format('d/m/Y') }}</div>
                            </div>
                        </div>

                        @if($rifa->prize_value)
                            <div class="mt-4 bg-gradient-to-r from-amber-400 to-amber-500 rounded-xl p-4 text-white">
                                <div class="text-sm opacity-90">Prêmio</div>
                                <div class="text-xl font-bold">R$ {{ number_format($rifa->prize_value, 2, ',', '.') }}</div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Number Grid --}}
        <div class="max-w-7xl mx-auto px-4 py-8">
            <h2 class="text-2xl font-bold text-gray-800 mb-6">Escolha seus números</h2>
            
            <livewire:rifa-grid :rifa="$rifa" />
        </div>

        {{-- Cart Summary --}}
        <livewire:cart-summary :rifa="$rifa" />
    </div>
</x-app-layout>
```

- [ ] **Step 4: Add routes**

```php
// Add to routes/web.php

use App\Http\Controllers\RifaController;

Route::get('/', [RifaController::class, 'index'])->name('rifas');
Route::get('/rifa/{slug}', [RifaController::class, 'show'])->name('rifa.show');
```

- [ ] **Step 5: Commit**

```bash
git add app/Http/Controllers/RifaController.php resources/views/rifa/ routes/web.php
git commit -m "feat: add public rifa pages with listing and detail views"
```

---

## Task 7: Admin Dashboard & CRUD

**Covers:** Admin authentication, rifa management, number status management

**Files:**
- Create: `app/Http/Controllers/Admin/DashboardController.php`
- Create: `app/Http/Controllers/Admin/RifaController.php`
- Create: `app/Livewire/Admin/RifaForm.php`
- Create: `app/Livewire/Admin/NumberManager.php`
- Create: Multiple admin views

- [ ] **Step 1: Install Laravel Breeze**

Run: `composer require laravel/breeze --dev`
Run: `php artisan breeze:install blade`
Run: `npm install && npm run build`

- [ ] **Step 2: Create Admin Dashboard Controller**

```php
<?php
// app/Http/Controllers/Admin/DashboardController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Rifa;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_rifas' => Rifa::count(),
            'active_rifas' => Rifa::where('status', 'active')->count(),
            'total_users' => User::count(),
            'total_revenue' => Payment::where('status', 'completed')->sum('amount'),
            'recent_payments' => Payment::with(['user', 'rifa'])
                ->where('status', 'completed')
                ->latest()
                ->take(10)
                ->get(),
        ];

        return view('admin.dashboard', compact('stats'));
    }
}
```

- [ ] **Step 3: Create Admin Rifa Controller**

```php
<?php
// app/Http/Controllers/Admin/RifaController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Rifa;
use App\Models\RifaNumber;
use App\Services\RifaService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class RifaController extends Controller
{
    public function index()
    {
        $rifas = Rifa::withCount('numbers')
            ->latest()
            ->paginate(10);

        return view('admin.rifas.index', compact('rifas'));
    }

    public function create()
    {
        return view('admin.rifas.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'prize_image' => 'nullable|image|max:2048',
            'number_price' => 'required|numeric|min:0.01',
            'total_numbers' => 'required|integer|min:1',
            'prize_value' => 'nullable|numeric|min:0',
            'draw_date' => 'required|date|after:now',
        ]);

        if ($request->hasFile('prize_image')) {
            $validated['prize_image'] = $request->file('prize_image')->store('rifas', 'public');
        }

        $validated['slug'] = Str::slug($validated['title']);
        $validated['status'] = 'active';

        $rifa = Rifa::create($validated);

        // Create all numbers
        $numbers = [];
        for ($i = 1; $i <= $rifa->total_numbers; $i++) {
            $numbers[] = [
                'rifa_id' => $rifa->id,
                'number' => $i,
                'status' => 'available',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        
        RifaNumber::insert($numbers);

        return redirect()->route('admin.rifas.index')
            ->with('success', 'Rifa criada com sucesso!');
    }

    public function edit(Rifa $rifa)
    {
        return view('admin.rifas.edit', compact('rifa'));
    }

    public function update(Request $request, Rifa $rifa)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'prize_image' => 'nullable|image|max:2048',
            'number_price' => 'required|numeric|min:0.01',
            'prize_value' => 'nullable|numeric|min:0',
            'draw_date' => 'required|date',
            'status' => 'required|in:active,closed',
        ]);

        if ($request->hasFile('prize_image')) {
            $validated['prize_image'] = $request->file('prize_image')->store('rifas', 'public');
        }

        $rifa->update($validated);

        return redirect()->route('admin.rifas.index')
            ->with('success', 'Rifa atualizada com sucesso!');
    }

    public function destroy(Rifa $rifa)
    {
        $rifa->delete();
        
        return redirect()->route('admin.rifas.index')
            ->with('success', 'Rifa removida com sucesso!');
    }

    public function numbers(Rifa $rifa)
    {
        $rifa->load('numbers');
        
        return view('admin.rifas.numbers', compact('rifa'));
    }

    public function updateNumberStatus(Request $request, Rifa $rifa)
    {
        $validated = $request->validate([
            'number_id' => 'required|exists:rifa_numbers,id',
            'status' => 'required|in:available,reserved,paid',
        ]);

        RifaNumber::where('id', $validated['number_id'])
            ->update(['status' => $validated['status']]);

        return back()->with('success', 'Status atualizado!');
    }
}
```

- [ ] **Step 4: Create RifaForm Livewire component**

```php
<?php
// app/Livewire/Admin/RifaForm.php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithFileUploads;

class RifaForm extends Component
{
    use WithFileUploads;

    public $rifa;
    public $title = '';
    public $description = '';
    public $number_price = '';
    public $total_numbers = '';
    public $prize_value = '';
    public $draw_date = '';
    public $prize_image;
    public $isEdit = false;

    public function mount($rifa = null)
    {
        if ($rifa) {
            $this->rifa = $rifa;
            $this->isEdit = true;
            $this->title = $rifa->title;
            $this->description = $rifa->description;
            $this->number_price = $rifa->number_price;
            $this->total_numbers = $rifa->total_numbers;
            $this->prize_value = $rifa->prize_value;
            $this->draw_date = $rifa->draw_date->format('Y-m-d');
        }
    }

    public function submit()
    {
        $this->validate([
            'title' => 'required|string|max:255',
            'number_price' => 'required|numeric|min:0.01',
            'total_numbers' => 'required|integer|min:1',
            'draw_date' => 'required|date|after:now',
        ]);

        $this->dispatch('saveRifa', [
            'title' => $this->title,
            'description' => $this->description,
            'number_price' => $this->number_price,
            'total_numbers' => $this->total_numbers,
            'prize_value' => $this->prize_value,
            'draw_date' => $this->draw_date,
            'prize_image' => $this->prize_image,
        ]);
    }

    public function render()
    {
        return view('livewire.admin.rifa-form');
    }
}
```

- [ ] **Step 5: Create NumberManager Livewire component**

```php
<?php
// app/Livewire/Admin/NumberManager.php

namespace App\Livewire\Admin;

use App\Models\Rifa;
use App\Models\RifaNumber;
use Livewire\Component;

class NumberManager extends Component
{
    public Rifa $rifa;
    public $filter = 'all';
    public $search = '';

    public function mount(Rifa $rifa): void
    {
        $this->rifa = $rifa;
    }

    public function updateStatus(int $numberId, string $status): void
    {
        RifaNumber::where('id', $numberId)->update(['status' => $status]);
        
        $this->dispatch('numberUpdated');
    }

    public function getNumbers()
    {
        $query = $this->rifa->numbers();
        
        if ($this->filter !== 'all') {
            $query->where('status', $this->filter);
        }
        
        if ($this->search) {
            $query->where('number', 'like', "%{$this->search}%");
        }
        
        return $query->orderBy('number')->paginate(50);
    }

    public function render()
    {
        return view('livewire.admin.number-manager', [
            'numbers' => $this->getNumbers(),
        ]);
    }
}
```

- [ ] **Step 6: Add admin routes**

```php
// Add to routes/web.php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\RifaController as AdminRifaController;

Route::prefix('admin')->name('admin.')->middleware(['auth', 'verified'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    
    Route::resource('rifas', AdminRifaController::class);
    Route::get('rifas/{rifa}/numbers', [AdminRifaController::class, 'numbers'])->name('rifas.numbers');
    Route::post('rifas/{rifa}/numbers/update', [AdminRifaController::class, 'updateNumberStatus'])->name('rifas.numbers.update');
});
```

- [ ] **Step 7: Commit**

```bash
git add app/Http/Controllers/Admin/ app/Livewire/Admin/ resources/views/admin/ resources/views/livewire/admin/ routes/web.php
git commit -m "feat: add admin dashboard with rifa CRUD and number management"
```

---

## Task 8: Sorteio (Draw) System

**Covers:** Draw system with random and manual winner selection

**Files:**
- Create: `app/Http/Controllers/Admin/SorteioController.php`
- Create: `resources/views/admin/sorteio/index.blade.php`

- [ ] **Step 1: Create SorteioController**

```php
<?php
// app/Http/Controllers/Admin/SorteioController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Rifa;
use App\Services\RifaService;
use Illuminate\Http\Request;

class SorteioController extends Controller
{
    public function index()
    {
        $rifas = Rifa::whereIn('status', ['closed', 'drawn'])
            ->with('winner')
            ->latest()
            ->get();

        return view('admin.sorteio.index', compact('rifas'));
    }

    public function draw(Request $request, Rifa $rifa, RifaService $rifaService)
    {
        $this->authorize('admin');
        
        try {
            $winner = $rifaService->drawWinner($rifa);
            
            return back()->with('success', "Ganhador sorteado! Número: {$winner->winning_number}");
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function manualDraw(Request $request, Rifa $rifa, RifaService $rifaService)
    {
        $this->authorize('admin');
        
        $validated = $request->validate([
            'number' => 'required|integer|min:1|max:' . $rifa->total_numbers,
            'notes' => 'nullable|string',
        ]);

        try {
            $winner = $rifaService->setManualWinner(
                $rifa, 
                $validated['number'], 
                $validated['notes']
            );
            
            return back()->with('success', "Ganhador definido manualmente! Número: {$winner->winning_number}");
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
```

- [ ] **Step 2: Create sorteio index view**

```blade
{{-- resources/views/admin/sorteio/index.blade.php --}}

<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <h1 class="text-2xl font-bold mb-6">Sistema de Sorteio</h1>

            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    {{ session('error') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                <h2 class="text-lg font-semibold mb-4">Rifas para Sorteio</h2>

                @forelse($rifas as $rifa)
                    <div class="border rounded-lg p-4 mb-4 {{ $rifa->status === 'drawn' ? 'bg-gray-50' : '' }}">
                        <div class="flex justify-between items-start">
                            <div>
                                <h3 class="font-semibold">{{ $rifa->title }}</h3>
                                <p class="text-sm text-gray-500">
                                    Status: 
                                    <span class="font-medium {{ $rifa->status === 'drawn' ? 'text-green-600' : 'text-yellow-600' }}">
                                        {{ $rifa->status === 'drawn' ? 'Sorteada' : 'Aguardando sorteio' }}
                                    </span>
                                </p>
                                @if($rifa->winner)
                                    <p class="text-sm text-green-600 mt-2">
                                        Ganhador: Número {{ str_pad($rifa->winner->winning_number, 3, '0', STR_PAD_LEFT) }}
                                        ({{ $rifa->winner->user->name ?? 'N/A' }})
                                    </p>
                                @endif
                            </div>

                            @if($rifa->status === 'closed')
                                <div class="flex gap-2">
                                    {{-- Random Draw --}}
                                    <form action="{{ route('admin.sorteio.draw', $rifa) }}" method="POST" 
                                          onsubmit="return confirm('Tem certeza que deseja sortear um ganhador?')">
                                        @csrf
                                        <button type="submit" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded text-sm">
                                            Sortear Aleatório
                                        </button>
                                    </form>

                                    {{-- Manual Draw --}}
                                    <form action="{{ route('admin.sorteio.manual', $rifa) }}" method="POST">
                                        @csrf
                                        <input type="number" name="number" min="1" max="{{ $rifa->total_numbers }}" 
                                               placeholder="Nº" required class="w-20 border rounded px-2 py-1 text-sm">
                                        <input type="text" name="notes" placeholder="Observação" 
                                               class="border rounded px-2 py-1 text-sm ml-1">
                                        <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded text-sm ml-1">
                                            Definir
                                        </button>
                                    </form>
                                </div>
                            @endif
                        </div>
                    </div>
                @empty
                    <p class="text-gray-500">Nenhuma rifa aguardando sorteio.</p>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
```

- [ ] **Step 3: Add sorteio routes**

```php
// Add to routes/web.php admin group

use App\Http\Controllers\Admin\SorteioController;

Route::prefix('admin')->name('admin.')->middleware(['auth', 'verified'])->group(function () {
    // ... existing routes ...
    
    Route::get('sorteio', [SorteioController::class, 'index'])->name('sorteio.index');
    Route::post('sorteio/{rifa}/draw', [SorteioController::class, 'draw'])->name('sorteio.draw');
    Route::post('sorteio/{rifa}/manual', [SorteioController::class, 'manualDraw'])->name('sorteio.manual');
});
```

- [ ] **Step 4: Commit**

```bash
git add app/Http/Controllers/Admin/SorteioController.php resources/views/admin/sorteio/ routes/web.php
git commit -m "feat: add sorteio (draw) system with random and manual selection"
```

---

## Task 9: Final Testing & Verification

**Covers:** End-to-end testing, verification of all features

- [ ] **Step 1: Run migrations**

Run: `php artisan migrate:fresh --seed`

- [ ] **Step 2: Create test data**

Run: `php artisan tinker`

```php
$rifa = App\Models\Rifa::create([
    'title' => 'iPhone 15 Pro',
    'slug' => 'iphone-15-pro',
    'description' => 'Ganhe um iPhone 15 Pro!',
    'number_price' => 10.00,
    'total_numbers' => 100,
    'prize_value' => 5000.00,
    'status' => 'active',
    'draw_date' => now()->addDays(7),
]);

for ($i = 1; $i <= 100; $i++) {
    App\Models\RifaNumber::create([
        'rifa_id' => $rifa->id,
        'number' => $i,
        'status' => 'available',
    ]);
}
```

- [ ] **Step 3: Test public pages**

Run: `php artisan serve`
Visit: http://localhost:8000
Expected: Rifa listing page shows

Visit: http://localhost:8000/rifa/iphone-15-pro
Expected: Rifa detail page with number grid

- [ ] **Step 4: Test admin pages**

Visit: http://localhost:8000/admin
Expected: Dashboard with stats

Visit: http://localhost:8000/admin/rifas
Expected: Rifa CRUD list

- [ ] **Step 5: Run tests**

Run: `php artisan test`
Expected: All tests pass

- [ ] **Step 6: Verify build**

Run: `npm run build`
Expected: Build completes without errors

- [ ] **Step 7: Final commit**

```bash
git add .
git commit -m "feat: complete rifa platform implementation"
```

---

## Summary

This plan implements a complete online raffle platform with:

1. **Database Schema**: 4 tables with proper relationships and indexes
2. **Models**: Eloquent models with relationships and scopes
3. **Business Logic**: RifaService with concurrency control (lockForUpdate) and caching
4. **Livewire Components**: RifaGrid and CartSummary with glassmorphism UI
5. **Public Pages**: Listing and detail pages
6. **Admin Dashboard**: Full CRUD for rifas and number management
7. **Sorteio System**: Random and manual winner selection

**Key Features:**
- Concurrency control via `DB::transaction()` with `lockForUpdate()`
- File-based caching via `Cache::remember()` for shared hosting
- Glassmorphism UI with TailwindCSS
- Dynamic cart without page reload
- Admin number status management

**Total Tasks:** 9
**Estimated Time:** 2-3 hours

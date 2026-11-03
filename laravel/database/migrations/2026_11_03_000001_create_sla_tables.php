<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();          // KG, GL, KSL ...
            $table->string('name');
            $table->string('contact_person')->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('applications', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();          // HRM, ATT ...
            $table->string('name');
            $table->timestamps();
        });

        // One client using one application (karimgroup + HRM)
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('application_id')->constrained()->cascadeOnDelete();
            $table->decimal('quarterly_amount', 12, 2)->default(0);
            $table->string('install_id', 64)->nullable();  // bound on first activation
            $table->boolean('is_active')->default(true);
            $table->text('note')->nullable();
            $table->timestamps();
            $table->unique(['client_id', 'application_id']);
        });

        Schema::create('sla_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscription_id')->constrained()->cascadeOnDelete();
            $table->date('period_from');
            $table->date('period_to');
            $table->decimal('amount', 12, 2);
            $table->date('paid_at');
            $table->string('method', 50)->nullable();       // cash, bank, bkash ...
            $table->string('reference')->nullable();
            $table->text('note')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('activation_codes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscription_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sla_payment_id')->nullable()->constrained()->nullOnDelete();
            $table->string('code', 40)->unique();           // KG-7F3A-92BC-11DE
            $table->string('license_id', 24)->unique();     // "lid" inside the signed token
            $table->date('valid_from');
            $table->date('valid_until');
            $table->string('install_id', 64)->nullable();
            $table->string('hostname')->nullable();
            $table->timestamp('activated_at')->nullable();
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->string('revoke_reason')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('activation_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscription_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action', 20);                   // activate, status
            $table->string('client_code', 20)->nullable();
            $table->string('app_code', 20)->nullable();
            $table->string('code', 40)->nullable();
            $table->string('install_id', 64)->nullable();
            $table->string('ip', 45)->nullable();
            $table->boolean('success');
            $table->string('message')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activation_logs');
        Schema::dropIfExists('activation_codes');
        Schema::dropIfExists('sla_payments');
        Schema::dropIfExists('subscriptions');
        Schema::dropIfExists('applications');
        Schema::dropIfExists('clients');
    }
};

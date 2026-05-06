<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('link_clicks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_link_id')->constrained('user_links')->cascadeOnDelete();
            $table->longText('link_clicked');
            $table->timestamp('clicked_at')->useCurrent();
            $table->string('browser')->nullable();
            $table->string('ip')->nullable();
            $table->string('device')->nullable();
            $table->string('location')->nullable();
            $table->string('referer')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('link_clicks');
    }
};

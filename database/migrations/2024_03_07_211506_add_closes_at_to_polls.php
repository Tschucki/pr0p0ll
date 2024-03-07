<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('polls', static function (Blueprint $table) {
            $table->after('closes_after', function (Blueprint $table) {
                $table->dateTime('closes_at')->nullable();
            });
        });
    }
};

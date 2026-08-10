<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->string('image')->nullable();
            $table->longText('description');
            $table->integer('popular')->default(0); 
            $table->integer('trending')->default(0); 
            $table->integer('status')->default(1);  
            $table->timestamps();
        });
    }



    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
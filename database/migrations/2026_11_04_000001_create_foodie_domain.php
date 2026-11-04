<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['category', 'cuisine', 'cooking_style', 'ingredient', 'tag', 'recommendation_label'] as $name) {
            Schema::create($name, function (Blueprint $table) use ($name) {
                $table->id();
                if (in_array($name, ['category', 'cuisine'])) {
                    $table->foreignId('parent_id')->nullable()->constrained($name)->restrictOnDelete();
                }
                $table->string('name', 160);
                $table->string('slug', 180)->unique();
                if ($name === 'cuisine') {
                    $table->enum('classification', ['region', 'tradition', 'editorial'])->default('tradition');
                }
                $table->boolean('is_active')->default(true);
                $table->unsignedInteger('sort_order')->default(0);
                $table->timestamps();
            });
        }
        Schema::create('food_type', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->nullable()->constrained('category')->restrictOnDelete();
            $table->string('name', 160);
            $table->string('slug', 180)->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
        Schema::create('allergen', function (Blueprint $table) {
            $table->id();
            $table->string('code', 40)->unique();
            $table->string('label_id', 160);
            $table->string('label_en', 160);
            $table->text('reference_url');
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
        Schema::create('place', function (Blueprint $table) {
            $table->id();
            $table->string('name', 160);
            $table->string('slug', 180)->unique();
            $table->string('normalized_name', 160)->index();
            $table->text('address')->nullable();
            $table->string('city_name', 120)->nullable();
            $table->string('city_slug', 160)->nullable()->index();
            $table->string('province_name', 120)->nullable();
            $table->string('maps_url', 2048)->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['city_name', 'id']);
        });
        Schema::create('visit', function (Blueprint $table) {
            $table->id();
            $table->foreignId('place_id')->constrained('place')->restrictOnDelete();
            $table->foreignId('user_id')->constrained('user')->restrictOnDelete();
            $table->uuid('creation_token');
            $table->unique(['user_id', 'creation_token']);
            $table->unsignedInteger('version')->default(1);
            $table->string('slug', 180)->unique();
            $table->string('title', 200)->nullable();
            $table->dateTime('visited_at')->nullable();
            $table->enum('status', ['draft', 'private', 'published'])->default('draft');
            foreach (['experience', 'service_review', 'atmosphere_review', 'cleanliness_review', 'value_review', 'conclusion'] as $field) {
                $table->text($field)->nullable();
            }
            foreach (['service_rating', 'atmosphere_rating', 'cleanliness_rating', 'value_rating'] as $field) {
                $table->decimal($field, 2, 1)->nullable();
            }
            $table->unsignedBigInteger('total_spent_idr')->nullable();
            $table->unsignedBigInteger('cover_media_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['status', 'visited_at', 'id']);
            $table->index(['place_id', 'status', 'visited_at', 'id']);
        });
        Schema::create('visit_item', function (Blueprint $table) {
            $table->id();
            $table->foreignId('visit_id')->constrained('visit')->restrictOnDelete();
            $table->foreignId('food_type_id')->nullable()->constrained('food_type')->restrictOnDelete();
            $table->string('menu_name', 200);
            $table->decimal('quantity', 8, 2)->nullable();
            $table->unsignedBigInteger('unit_price_idr')->nullable();
            $table->decimal('taste_rating', 2, 1)->nullable();
            $table->text('notes')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->index(['visit_id', 'sort_order']);
            $table->index(['food_type_id', 'id']);
        });
        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('visit_id')->constrained('visit')->restrictOnDelete();
            $table->uuid('uuid')->unique();
            $table->string('original_path');
            $table->string('mime_type', 80);
            $table->unsignedBigInteger('original_bytes');
            $table->unsignedInteger('width');
            $table->unsignedInteger('height');
            $table->string('sha256', 64);
            $table->string('alt_text', 300)->nullable();
            $table->enum('processing_status', ['pending', 'processing', 'ready', 'failed'])->default('pending');
            $table->string('processing_error')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();
            $table->index(['visit_id', 'sort_order']);
        });
        Schema::table('visit', fn (Blueprint $table) => $table->foreign('cover_media_id')->references('id')->on('media')->nullOnDelete());
        Schema::create('media_variant', function (Blueprint $table) {
            $table->id();
            $table->foreignId('media_id')->constrained('media')->cascadeOnDelete();
            $table->string('variant', 24);
            $table->string('path');
            $table->string('mime_type', 80);
            $table->unsignedInteger('width');
            $table->unsignedInteger('height');
            $table->unsignedBigInteger('bytes');
            $table->enum('status', ['pending', 'processing', 'ready', 'failed'])->default('ready');
            $table->timestamps();
            $table->unique(['media_id', 'variant']);
        });
        foreach ([
            ['visit_item_cuisine', 'visit_item', 'cuisine'], ['visit_item_cooking_style', 'visit_item', 'cooking_style'],
            ['visit_item_ingredient', 'visit_item', 'ingredient'], ['visit_item_allergen', 'visit_item', 'allergen'],
            ['ingredient_allergen', 'ingredient', 'allergen'], ['visit_item_media', 'visit_item', 'media'],
            ['visit_tag', 'visit', 'tag'], ['visit_recommendation_label', 'visit', 'recommendation_label'],
        ] as [$name, $left, $right]) {
            Schema::create($name, function (Blueprint $table) use ($name, $left, $right) {
                $table->foreignId($left.'_id')->constrained($left)->cascadeOnDelete();
                $table->foreignId($right.'_id')->constrained($right)->restrictOnDelete();
                $table->unique([$left.'_id', $right.'_id'], $name.'_pair_unique');
                $table->index([$right.'_id', $left.'_id'], $name.'_reverse_index');
                if ($name === 'visit_item_ingredient') {
                    $table->string('knowledge_source', 500)->nullable();
                    $table->text('notes')->nullable();
                }
                if ($name === 'visit_item_allergen') {
                    $table->enum('exposure_status', ['contains', 'may_contain']);
                    $table->string('evidence_source', 500);
                    $table->text('notes')->nullable();
                }
                if ($name === 'ingredient_allergen') {
                    $table->enum('relation_status', ['contains', 'may_contain']);
                    $table->text('notes')->nullable();
                }
            });
        }
        Schema::create('slug_redirect', function (Blueprint $table) {
            $table->id();
            $table->enum('entity_type', ['visit', 'place']);
            $table->unsignedBigInteger('entity_id');
            $table->string('old_slug', 180);
            $table->timestamp('created_at')->useCurrent();
            $table->boolean('allow_public_redirect')->default(false);
            $table->unique(['entity_type', 'old_slug']);
            $table->index(['entity_type', 'entity_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('slug_redirect');
        foreach (['visit_recommendation_label', 'visit_tag', 'visit_item_media', 'ingredient_allergen', 'visit_item_allergen', 'visit_item_ingredient', 'visit_item_cooking_style', 'visit_item_cuisine'] as $table) {
            Schema::dropIfExists($table);
        }
        Schema::table('visit', fn (Blueprint $table) => $table->dropForeign(['cover_media_id']));
        foreach (['media_variant', 'media', 'visit_item', 'visit', 'place', 'allergen', 'food_type', 'recommendation_label', 'tag', 'ingredient', 'cooking_style', 'cuisine', 'category'] as $table) {
            Schema::dropIfExists($table);
        }
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('app_store_connect_signs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workspace_id')
                ->references('id')
                ->on('workspaces')
                ->onDelete('cascade');
            $table->string('cert')->nullable();
            $table->string('provision_profile')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('app_store_connect_signs');
    }
};
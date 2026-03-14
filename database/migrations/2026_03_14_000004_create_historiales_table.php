<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHistorialesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('historiales', function (Blueprint $table) {
            $table->id();
            $table->integer('cantidad');
            $table->foreignId('id_bodega_origen')->nullable()->constrained('bodegas')->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('id_bodega_destino')->nullable()->constrained('bodegas')->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('id_inventario')->constrained('inventarios')->cascadeOnUpdate()->restrictOnDelete();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
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
        Schema::dropIfExists('historiales');
    }
}

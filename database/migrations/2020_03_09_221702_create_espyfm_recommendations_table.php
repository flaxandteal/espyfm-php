<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Flaxandteal\EspyFM\EspyFMService;

class CreateEspyfmRecommendationsTable extends Migration
{
    /**
     * Convert key type to column type
     *
     * @return string
     */
    public static function keyTypeToColumnType(string $keyType)
    {
        switch ($keyType) {
            case 'int':
            case 'integer':
                return 'integer';
            case 'uuid':
                return 'uuid';
            default:
                return $keyType;
        }
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $espyfm = app()->make(EspyFMService::class);
        $user = app()->make($espyfm->getRecommenderClass());
        $item = app()->make($espyfm->getRecommendedItemClass());

        Schema::table($user->getTable(), function (Blueprint $table) {
            $table->unsignedInteger('espyfm_lightfm_id')->nullable();
        });

        Schema::table($item->getTable(), function (Blueprint $table) {
            $table->unsignedInteger('espyfm_lightfm_id')->nullable();
        });

        Schema::create('espyfm_recommendations', function (Blueprint $table) use ($user, $item) {
            $table->addColumn(self::keyTypeToColumnType($user->getKeyType()), 'user_id');
            $table->addColumn(self::keyTypeToColumnType($item->getKeyType()), 'item_id');

            $table->foreign('user_id')
              ->references($user->getKeyName())
              ->on($user->getTable())
              ->onDelete('cascade');

            $table->foreign('item_id')
              ->references($item->getKeyName())
              ->on($item->getTable())
              ->onDelete('cascade');

            $table->primary(['user_id', 'item_id']);

            $table->integer('score')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $espyfm = app()->make(EspyFMService::class);
        $user = app()->make($espyfm->getRecommenderClass());
        $item = app()->make($espyfm->getRecommendedItemClass());

        Schema::table($user->getTable(), function (Blueprint $table) {
            $table->dropColumn('espyfm_lightfm_id');
        });

        Schema::table($item->getTable(), function (Blueprint $table) {
            $table->dropColumn('espyfm_lightfm_id');
        });

        Schema::dropIfExists('espyfm_recommendations');
    }
}

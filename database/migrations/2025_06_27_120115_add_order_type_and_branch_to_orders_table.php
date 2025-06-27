

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
        Schema::table('orders', function (Blueprint $table) {
            // Add order_type column if it doesn't exist
            if (!Schema::hasColumn('orders', 'order_type')) {
                $table->string('order_type')->default('takeaway')->after('id');
            }
            
            // Add branch_id column if it doesn't exist
            if (!Schema::hasColumn('orders', 'branch_id')) {
                $table->unsignedBigInteger('branch_id')->nullable()->after('order_type');
                $table->foreign('branch_id')->references('id')->on('branches')->onDelete('set null');
            }
            
            // Add state transition timestamps
            if (!Schema::hasColumn('orders', 'submitted_at')) {
                $table->timestamp('submitted_at')->nullable();
            }
            if (!Schema::hasColumn('orders', 'confirmed_at')) {
                $table->timestamp('confirmed_at')->nullable();
            }
            if (!Schema::hasColumn('orders', 'preparing_at')) {
                $table->timestamp('preparing_at')->nullable();
            }
            if (!Schema::hasColumn('orders', 'ready_at')) {
                $table->timestamp('ready_at')->nullable();
            }
            if (!Schema::hasColumn('orders', 'completed_at')) {
                $table->timestamp('completed_at')->nullable();
            }
            if (!Schema::hasColumn('orders', 'cancelled_at')) {
                $table->timestamp('cancelled_at')->nullable();
            }
            
            // Add order number if it doesn't exist
            if (!Schema::hasColumn('orders', 'order_number')) {
                $table->string('order_number')->unique()->nullable()->after('id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'order_type',
                'submitted_at',
                'confirmed_at',
                'preparing_at',
                'ready_at',
                'completed_at',
                'cancelled_at',
                'order_number'
            ]);
            
            $table->dropForeign(['branch_id']);
            $table->dropColumn('branch_id');
        });
    }
};

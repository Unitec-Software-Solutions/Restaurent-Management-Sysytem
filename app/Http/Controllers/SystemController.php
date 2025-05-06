<?php

namespace App\Http\Controllers;

use App\Models\SystemSetting;
use App\Models\PaymentGateway;
use App\Models\NotificationProvider;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SystemController extends Controller
{
    public function getSettings(Request $request)
    {
        $settings = SystemSetting::where('is_public', true)
            ->orWhere(function ($query) {
                $query->where('is_public', false)
                    ->where('group', '!=', 'security');
            })
            ->get();

        return response()->json(['settings' => $settings]);
    }

    public function updateSetting(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'key' => 'required|exists:system_settings,key',
            'value' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $setting = SystemSetting::where('key', $request->key)->first();
        $oldValue = $setting->value;

        SystemSetting::setValue($request->key, $request->value);

        // Log the change
        AuditLog::create([
            'action' => 'update_setting',
            'model_type' => SystemSetting::class,
            'model_id' => $setting->id,
            'user_id' => $request->user()->id,
            'old_values' => ['value' => $oldValue],
            'new_values' => ['value' => $request->value],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return response()->json([
            'message' => 'Setting updated successfully',
            'setting' => $setting->fresh(),
        ]);
    }

    public function configurePaymentGateway(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'provider' => 'required|string|max:255',
            'credentials' => 'required|array',
            'is_active' => 'boolean',
            'is_test_mode' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $gateway = PaymentGateway::updateOrCreate(
            ['provider' => $request->provider],
            $request->all()
        );

        // Log the change
        AuditLog::create([
            'action' => 'configure_payment_gateway',
            'model_type' => PaymentGateway::class,
            'model_id' => $gateway->id,
            'user_id' => $request->user()->id,
            'new_values' => $request->all(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return response()->json([
            'message' => 'Payment gateway configured successfully',
            'gateway' => $gateway,
        ]);
    }

    public function configureNotificationProvider(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'type' => 'required|in:sms,email,push',
            'credentials' => 'required|array',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $provider = NotificationProvider::updateOrCreate(
            ['type' => $request->type],
            $request->all()
        );

        // Log the change
        AuditLog::create([
            'action' => 'configure_notification_provider',
            'model_type' => NotificationProvider::class,
            'model_id' => $provider->id,
            'user_id' => $request->user()->id,
            'new_values' => $request->all(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return response()->json([
            'message' => 'Notification provider configured successfully',
            'provider' => $provider,
        ]);
    }

    public function getAuditLogs(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'action' => 'nullable|string',
            'model_type' => 'nullable|string',
            'user_id' => 'nullable|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $query = AuditLog::whereBetween('created_at', [$request->start_date, $request->end_date]);

        if ($request->action) {
            $query->where('action', $request->action);
        }

        if ($request->model_type) {
            $query->where('model_type', $request->model_type);
        }

        if ($request->user_id) {
            $query->where('user_id', $request->user_id);
        }

        $logs = $query->with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(50);

        return response()->json(['logs' => $logs]);
    }
} 
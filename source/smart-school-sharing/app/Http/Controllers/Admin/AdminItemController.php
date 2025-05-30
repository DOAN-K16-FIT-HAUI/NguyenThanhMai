<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AdminItemController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $items = Item::withoutGlobalScope('not_deleted')
            ->with('user', 'category')
            ->when($search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            })
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.items.index', compact('items', 'search'));
    }


    public function approve(Item $item)
    {
        $item->update([
            'status' => 'available',
            'updated_by' => Auth::id(),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Item approved!');
    }

    public function destroy(Item $item)
    {
        try {
            $item->del_flag = true;
            $item->save();
            return redirect()->route('admin.items.index')->with('success', 'Đã xóa item thành công.');
        } catch (\Exception $e) {
            Log::error('Lỗi khi xóa item', ['error' => $e->getMessage(), 'item_id' => $item->id]);
            return back()->with('error', 'Xóa item thất bại.');
        }
    }

    public function reject(Request $request, Item $item)
    {
        $validated = $request->validate([
            'reason' => 'required|string|max:1000',
        ]);
        $item->load('user');

        if (!$item->user) {
            return back()
                ->with('error', 'Cannot reject item - no user associated');
        }

        try {
            \DB::transaction(function () use ($item, $validated) {
                // Cập nhật trạng thái item
                $item->update([
                    'status' => 'rejected',
                    'updated_at' => now(),
                ]);

                // Lưu lý do từ chối
                \DB::table('tb_item_rejections')->insert([
                    'item_id' => $item->id,
                    'reason' => $validated['reason'],
                    'rejected_by' => auth()->id(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            });

            // Gửi email thông báo
            Log::info("item : " . $item);
            $emailService = app('email-service');
            $emailSent = $emailService->sendItemRejectedNotification($item, $validated['reason']);

            return redirect()
                ->route('admin.items.index')
                ->with('success', 'Item rejected successfully.')
                ->with('email_sent', $emailSent);

        } catch (\Exception $e) {
            \Log::error('Item rejection failed: ' . $e->getMessage());

            return back()
                ->withInput()
                ->with('error', 'Failed to reject item. Please try again.');
        }
    }

    public function show(Item $item)
    {
        // Load relationships: user, category, images
        $item->load(['user', 'category', 'images']);

        // Lấy danh sách ảnh và chuyển sang dạng đường dẫn đầy đủ
        $images = $item->images->map(function($img) {
            return asset('' . ltrim($img->image_url, '/'));
        })->toArray();

        // Lấy lý do từ chối gần nhất (nếu có)
        $rejectionReason = \DB::table('tb_item_rejections')
            ->where('item_id', $item->id)
            ->latest()
            ->first();
        // Truyền thêm $images vào view
        return view('admin.items.show', compact('item', 'rejectionReason', 'images'));
    }

}

<?php

namespace App\Http\Controllers\Api;
use App\Http\Resources\User\UserCollection;
use App\Http\Resources\User\UserResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\User;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use Intervention\Image\ImageManager;
class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        // Optimize: Select only needed columns and eager load image relationship efficiently
        $users = User::select('id', 'name', 'email', 'phone', 'created_at', 'updated_at')
            ->with(['image:id,url,imageable_id,imageable_type'])
            ->paginate(20);

        return UserCollection::make(
            UserResource::collection($users)
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUserRequest $request)
    {
        $data = $request->validated();
        $data['password'] = 'password123';
        $user = User::create($data);
        return UserResource::make($user);
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        //
        return UserResource::make($user->load('image'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRequest $request, User $user)
    {
        $user->update($request->validated());

        if ($request->hasFile('image')) {
             if ($user->image) {
                // Delete old file from storage
                $oldPath = str_replace('storage/', '', $user->image->url);
                \Illuminate\Support\Facades\Storage::disk('public')->delete($oldPath);
                
                $user->image()->delete();
             }
             $path = $this->compressAndStore($request->file('image'));
             $user->image()->create(['url' => 'storage/' . $path]);
        }
        
        return UserResource::make($user->load('image'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        $user->delete();
        return response()->noContent();
    }

    private function compressAndStore($file)
    {
        $manager = app(\Intervention\Image\ImageManager::class);
        $image = $manager->read($file);
        
        // Resize aggressively to 200px max (creates small thumbnails)
        if ($image->width() > 200 || $image->height() > 200) {
            $image->scaleDown(width: 200, height: 200);
        }
        
        // Compress heavily with 50% quality
        $filename = uniqid() . '.jpg';
        $path = storage_path('app/public/images/' . $filename);
        
        // Ensure directory exists
        if (!file_exists(storage_path('app/public/images'))) {
            mkdir(storage_path('app/public/images'), 0755, true);
        }
        
        $image->toJpeg(50)->save($path);
        
        
        return 'images/' . $filename;
    }
}
<?php

namespace App\Http\Controllers;

use App\Models\Account;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class AccountController extends Controller
{
    /**
     * Create a new AccountController instance.
     *
     * @return void
     */
    public function __construct()
    {
        // We'll handle auth in routes instead
    }
    
    public function index(Request $request)
{
    $query = Account::with(['latest.latest', 'user']);

    // Filter by search term (e.g., code or user name, case-insensitive)
    if ($request->filled('search')) {
        $searchTerm = $request->search;
        $escapedSearchTerm = addcslashes($searchTerm, '%_');
        $searchTermLower = strtolower($escapedSearchTerm);
        $query->where(function ($q) use ($searchTermLower) {
            $q->orWhereHas('latest', function ($subQuery) use ($searchTermLower) {
                $subQuery->whereRaw('LOWER(code) LIKE ?', ['%' . $searchTermLower . '%']);
            })
            ->orWhereHas('user', function ($subQuery) use ($searchTermLower) {
                $subQuery->whereRaw('LOWER(name) LIKE ?', ['%' . $searchTermLower . '%']);
            });
        });
    }

    // Filter by status
    if ($request->filled('status') && $request->status != 'Status') {
        $statusValue = $request->status;
        $query->whereHas('latest', function ($subQuery) use ($statusValue) {
            $subQuery->where('status', $statusValue);
        });
    }

    // Pagination size (default 10, allowed: 5,10,25,50,100)
    $perPage = (int) ($request->per_page ?? 10);
    $perPage = in_array($perPage, [5, 10, 25, 50, 100]) ? $perPage : 10;

    $accounts = $query->orderBy('id', 'desc')->paginate($perPage);

    return view('accounts.index', compact('accounts'));
}
    
    public function create()
    {
        return view('accounts.create');
    }
    
    public function store(Request $request)
    {
        // Validation logic
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            // Add other fields as needed
        ]);
        
        Account::create([
            'user_id' => $validated['user_id'],
            'creator_id' => 1, // Default to admin user (you may want to adjust this)
        ]);
        
        return redirect()->route('accounts.index')->with('success', 'Account created successfully');
    }
    
    public function show(Account $account)
    {
        return view('accounts.show', compact('account'));
    }
    
    public function edit(Account $account)
    {
        return view('accounts.edit', compact('account'));
    }
    
    public function update(Request $request, Account $account)
    {
        // Validation logic
        $validated = $request->validate([
            'code' => 'required|string',
            'term' => 'required|integer|min:0',
            'limit' => 'required|numeric|min:0',
            'status' => 'required|string|in:Pending,Approve,Reject',
            'remark' => 'nullable|string',
        ]);
        
        // Create or update AccountDetail record
        $accountDetail = $account->latest;
        if (!$accountDetail) {
            $accountDetail = $account->account_details()->create([
                'code' => $validated['code'],
                'status' => $validated['status'],
                'remark' => $validated['remark'],
                'creator_id' => 1, // Default to admin or system user
            ]);
        } else {
            $accountDetail->update([
                'code' => $validated['code'],
                'status' => $validated['status'],
                'remark' => $validated['remark'],
            ]);
        }
        
        // Create or update AccountDetailItem
        $accountDetailItem = $accountDetail->latest;
        if (!$accountDetailItem) {
            $accountDetail->account_detail_items()->create([
                'term' => $validated['term'],
                'limit' => $validated['limit'],
                'creator_id' => 1, // Default to admin or system user
            ]);
        } else {
            $accountDetailItem->update([
                'term' => $validated['term'],
                'limit' => $validated['limit'],
            ]);
        }
        
        return redirect()->route('accounts.index')->with('success', 'Account updated successfully');
    }
    
    public function destroy(Account $account)
    {
        $account->delete();
        
        return redirect()->route('accounts.index')->with('success', 'Account deleted successfully');
    }
    
    /**
     * Get a JWT token via given credentials.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $credentials = $request->only('email', 'password');

        if (!$token = Auth::guard('api')->attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        
        $user = Auth::guard('api')->user();
        
        // Verify Firebase integration
        try {
            $auth = app('firebase.auth');
            
            // Check if user exists in Firebase
            try {
                // If user exists, we're good
                $auth->getUser("$user->id");
            } catch (\Exception $e) {
                // User doesn't exist in Firebase, create them
                try {
                    // Create properties for Firebase user
                    $userProperties = [
                        'uid' => "$user->id",
                        'email' => $user->email,
                        'emailVerified' => false,
                        'displayName' => strtoupper($user->name),
                        'disabled' => false,
                    ];
                    
                    // Add phone if available
                    if (!empty($user->phone)) {
                        $userProperties['phoneNumber'] = "+" . $user->phone;
                    }
                    
                    // Create the user in Firebase
                    $auth->createUser($userProperties);
                    
                    // Set custom claims
                    $auth->setCustomUserClaims("$user->id", [
                        'customer' => json_encode($user),
                    ]);
                } catch (\Exception $e) {
                    Log::warning("Failed to create Firebase user during login: {$e->getMessage()}");
                }
            }
        } catch (\Exception $e) {
            // Firebase service not available
            Log::info("Firebase Auth service not available during login: {$e->getMessage()}");
        }

        return $this->respondWithToken($token);
    }

    /**
     * Register a new user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|between:2,100',
            'email' => 'required|string|email|max:100|unique:users',
            'password' => 'required|string|min:6',
            'phone' => 'sometimes|string',
            'country_code' => 'sometimes|string',
        ]);

        if($validator->fails()){
            return response()->json($validator->errors(), 400);
        }
        
        // Check Firebase for existing user by email or phone before creating
        try {
            $auth = app('firebase.auth');
            
            // Check if email already exists in Firebase
            try {
                $auth->getUserByEmail($request->email);
                return response()->json(['error' => 'Email already exists in Firebase'], 400);
            } catch (\Exception $e) {
                // Email doesn't exist, proceed
            }
            
            // Check if phone already exists in Firebase (if phone is provided)
            if (!empty($request->phone)) {
                try {
                    $auth->getUserByPhoneNumber("+" . $request->phone);
                    return response()->json(['error' => 'Phone number already exists in Firebase'], 400);
                } catch (\Exception $e) {
                    // Phone doesn't exist, proceed
                }
            }
        } catch (\Exception $e) {
            // Firebase service not available, log and continue
            Log::warning("Firebase service not available during registration: {$e->getMessage()}");
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'country_code' => $request->country_code,
            'password' => bcrypt($request->password)
        ]);

        $token = Auth::guard('api')->login($user);
        
        return $this->respondWithToken($token);
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        return response()->json(Auth::guard('api')->user());
    }

    /**
     * API method to log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function apiLogout()
    {
        // Get user before logout
        $user = Auth::guard('api')->user();
        $userId = $user ? $user->id : null;
        
        // Log out from JWT
        Auth::guard('api')->logout();
        
        // Handle Firebase logout if user exists
        if ($userId) {
            try {
                $auth = app('firebase.auth');
                
                // Check if user exists in Firebase before updating
                try {
                    $auth->getUser("$userId");
                    
                    // You could add Firebase-specific logout actions here if needed
                    // For example, revoking tokens or updating user status
                    
                } catch (\Exception $e) {
                    // User doesn't exist in Firebase, just log it
                    Log::info("Firebase user with ID {$userId} not found during logout. Continuing with local logout.");
                }
            } catch (\Exception $e) {
                // Firebase service not available
                Log::info("Firebase Auth service not available during logout: {$e->getMessage()}");
            }
        }

        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        try {
            // Get the current user
            $user = Auth::guard('api')->user();
            // Force logout
            Auth::guard('api')->logout();
            // Generate new token
            $newToken = Auth::guard('api')->login($user);
            
            return $this->respondWithToken($newToken);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Token could not be refreshed, please login again'], 401);
        }
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => config('jwt.ttl') * 60, // Default TTL is usually 1 hour
            'user' => Auth::guard('api')->user()
        ]);
    }
}
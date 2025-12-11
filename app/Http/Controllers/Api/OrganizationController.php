/**
 * Store a newly created organization
 */
public function store(StoreOrganizationRequest $request)
{
    $validated = $request->validated();

    // Generate slug if not provided
    $validated['slug'] = $validated['slug'] ?? Str::slug($validated['name']);

    // Generate unique code
    $validated['code'] = strtoupper(Str::random(8));

    // Create organization
    $organization = Organization::create([
        'name' => $validated['name'],
        'slug' => $validated['slug'],
        'code' => $validated['code'],
        'domain' => $validated['domain'] ?? null,
        'is_active' => $validated['is_active'] ?? true,
    ]);

    // Create admin user if provided
    if (isset($validated['admin_email'])) {
        $user = User::firstOrCreate(
            ['email' => $validated['admin_email']],
            [
                'name' => $validated['admin_name'],
                'password' => Hash::make($validated['admin_password']),
                'is_super_admin' => false,
            ]
        );

        // Attach user to organization as admin
        $organization->users()->attach($user->id, ['role' => 'admin']);
    }

    return (new OrganizationResource($organization->load('users')))
        ->response()
        ->setStatusCode(201);
}

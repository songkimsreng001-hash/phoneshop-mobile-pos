<?php

namespace Tests\Unit;

use App\Models\Admin;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use PHPUnit\Framework\TestCase;

class AccessControlTest extends TestCase
{
    public function test_admin_can_access_only_assigned_shops(): void
    {
        $admin = new Admin();
        $admin->setRelation('shops', collect([
            new User(['id' => 42]),
        ]));

        $this->assertTrue($admin->canAccessShop(42));
        $this->assertFalse($admin->canAccessShop(99));
    }

    public function test_user_can_access_only_their_own_shop(): void
    {
        $user = new User(['id' => 7]);

        $this->assertTrue($user->canAccessShop(7));
        $this->assertFalse($user->canAccessShop(8));
    }
}

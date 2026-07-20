<?php

namespace Tests\Feature;

use Tests\TestCase;

class LayoutViewTest extends TestCase
{
    public function test_required_layout_views_exist(): void
    {
        $this->assertTrue(view()->exists('admin.layouts.main'));
        $this->assertTrue(view()->exists('admin.layouts.top_bar'));
        $this->assertTrue(view()->exists('shop.layouts.main'));
        $this->assertTrue(view()->exists('shop.layouts.top_bar'));
        $this->assertTrue(view()->exists('superadmin.layouts.main'));
        $this->assertTrue(view()->exists('superadmin.layouts.top_bar'));
    }
}

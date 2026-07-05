<?php

namespace App\Controller\Admin;

use App\Models\Dashboard;

class DashboardController
{
    public function index()
    {
        if(session_status()==PHP_SESSION_NONE){
            session_start();
        }

        if(!isset($_SESSION['user_info'])){
            header('Location: '.BASE_URL.'login');
            exit;
        }

        $user = $_SESSION['user_info'];
        $dashboard = new Dashboard();
        $stats = $dashboard->getStats();
        $bestSellers = $dashboard->getBestSellers();
        $monthlyRevenue = $dashboard->getRevenueChart();
        $recentOrders = $dashboard->getRecentOrders();
        $orderStatus = $dashboard->getOrderStatus();
        $topCustomers = $dashboard->getTopCustomers();
        $topRevenueProducts = $dashboard->getTopRevenueProducts();

        require __DIR__.'/../../Views/admin/dashboard.php';
    }
}
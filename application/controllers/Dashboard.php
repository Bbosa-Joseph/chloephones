<?php 

defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard extends Admin_Controller 
{
    public function __construct()
    {
        parent::__construct();

        $this->not_logged_in();

        $this->data['page_title'] = 'Dashboard';
        
        $this->load->model('model_products');
        $this->load->model('model_orders');
        $this->load->model('model_users');
        $this->load->model('model_stores');
    }

    /**
     * Dashboard main page
     * Passes total counts to the view
     */
    public function index()
    {
        // Products and inventory
        $this->data['total_products']   = $this->model_products->countTotalProducts();
        $this->data['total_brands']     = $this->model_products->countTotalBrands();
        $this->data['total_category']   = $this->model_products->countTotalCategory();
        $this->data['total_attributes'] = $this->model_products->countTotalAttribures();
        $this->data['out_of_stock']     = $this->model_products->countOutOfStock();
        $this->data['aged_products']    = $this->model_products->countAgedProducts();
        $this->data['total_stock_value']= $this->model_products->getTotalStockValue();

        // Orders
        $this->data['total_paid_orders']   = $this->model_orders->countTotalPaidOrders();
        $this->data['total_unpaid_orders'] = $this->model_orders->countTotalUnpaidOrders(); // make sure method exists

        // Users and stores
        $this->data['total_users']  = $this->model_users->countTotalUsers();
        $this->data['total_stores'] = $this->model_stores->countTotalStores();

        // Admin flag
        $user_id   = $this->session->userdata('id');
        $is_admin  = ($user_id == 1) ? true : false;
        $this->data['is_admin'] = $is_admin;

        // Render the dashboard view
        $this->render_template('dashboard', $this->data);
    }

    /*
    * AJAX endpoint: returns member's assigned products as JSON
    */
    public function memberProducts()
    {
        $user_id = $this->session->userdata('id');
        $this->load->model('model_stores');

        $warehouses = $this->db->query("SELECT id, name FROM stores WHERE assigned_user_id = ?", array($user_id))->result();
        $products = array();

        if (!empty($warehouses)) {
            $warehouse_map = array();
            $warehouse_ids = array();
            foreach ($warehouses as $w) {
                $warehouse_ids[] = (int) $w->id;
                $warehouse_map[$w->id] = $w->name;
            }

            $ids_placeholder = implode(',', array_fill(0, count($warehouse_ids), '?'));
            $rows = $this->db->query("SELECT * FROM products WHERE warehouse_id IN ($ids_placeholder)", $warehouse_ids)->result();

            foreach ($rows as $p) {
                $products[] = array(
                    'name'       => $p->name ?? 'N/A',
                    'imei'       => $p->imei ?? 'N/A',
                    'warehouse'  => isset($warehouse_map[$p->warehouse_id]) ? $warehouse_map[$p->warehouse_id] : '',
                    'status'     => ($p->availability == 1) ? 'Available' : 'Sold'
                );
            }
        }

        echo json_encode(array(
            'count'    => count($products),
            'products' => $products
        ));
    }
}
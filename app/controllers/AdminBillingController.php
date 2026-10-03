<?php

class AdminBillingController extends Controller
{
    public function invoices(): void
    {
        $this->requireAdmin();
        $this->view('admin/billing/invoices', ['title' => 'Invoices', 'invoices' => (new Order())->allInvoices()], 'layout/admin');
    }

    public function payments(): void
    {
        $this->requireAdmin();
        $this->view('admin/billing/payments', ['title' => 'Payments', 'payments' => (new Order())->allPayments()], 'layout/admin');
    }
}

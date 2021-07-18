 <?php $__env->startSection('content'); ?>
<?php if(empty($product_name)): ?>
<div class="alert alert-danger alert-dismissible text-center"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><?php echo e('No Data exist between this date range!'); ?></div>
<?php endif; ?>
<?php if(session()->has('not_permitted')): ?>
  <div class="alert alert-danger alert-dismissible text-center"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><?php echo e(session()->get('not_permitted')); ?></div> 
<?php endif; ?>

<section class="forms">
    <div class="container-fluid">
        <div class="card">
            <div class="card-header mt-2">
                <h3 class="text-center"><?php echo e(trans('file.Product Report')); ?></h3>
            </div>
            <?php echo Form::open(['route' => 'report.product', 'method' => 'post']); ?>

            <div class="row">
                <div class="col-md-4 offset-md-2 mt-3">
                    <div class="form-group row">
                        <label class="d-tc mt-2"><strong><?php echo e(trans('file.Choose Your Date')); ?></strong> &nbsp;</label>
                        <div class="d-tc">
                            <div class="input-group">
                                <input type="text" class="daterangepicker-field form-control" value="<?php echo e($start_date); ?> To <?php echo e($end_date); ?>" required />
                                <input type="hidden" name="start_date" value="<?php echo e($start_date); ?>" />
                                <input type="hidden" name="end_date" value="<?php echo e($end_date); ?>" />
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mt-3">
                    <div class="form-group row">
                        <label class="d-tc mt-2"><strong><?php echo e(trans('file.Choose Warehouse')); ?></strong> &nbsp;</label>
                        <div class="d-tc">
                            <input type="hidden" name="warehouse_id_hidden" value="<?php echo e($warehouse_id); ?>" />
                            <select id="warehouse_id" name="warehouse_id" class="selectpicker form-control" data-live-search="true" data-live-search-style="begins" >
                                <option value="0"><?php echo e(trans('file.All Warehouse')); ?></option>
                                <?php $__currentLoopData = $lims_warehouse_list; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $warehouse): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($warehouse->id); ?>"><?php echo e($warehouse->name); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="col-md-2 mt-3">
                    <div class="form-group">
                        <button class="btn btn-primary" type="submit"><?php echo e(trans('file.submit')); ?></button>
                    </div>
                </div>
            </div>
            <?php echo Form::close(); ?>


            <div class="table-responsive mb-4">
                <table id="report-table" class="table table-hover">
                    <thead>
                        <tr>
                            <th class="not-exported"></th>
                            <th><?php echo e(trans('file.Product Name')); ?></th>
                            <th><?php echo e(trans('file.Purchased Amount')); ?></th>
                            <th><?php echo e(trans('file.Purchased')); ?> <?php echo e(trans('file.qty')); ?></th>
                            <th><?php echo e(trans('file.Sold Amount')); ?></th>
                            <th><?php echo e(trans('file.Sold')); ?> <?php echo e(trans('file.qty')); ?></th>
                            <th><?php echo e(trans('file.profit')); ?></th>
                            <th><?php echo e(trans('file.In Stock')); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(!empty($product_name)): ?>
                        <?php $__currentLoopData = $product_id; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $pro_id): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td><?php echo e($key); ?></td>
                            <td><?php echo e($product_name[$key]); ?></td>
                            <?php
                                if($warehouse_id == 0){
                                    if($variant_id[$key]) {
                                        $purchased_cost = DB::table('product_purchases')->where([
                                            ['product_id', $pro_id],
                                            ['variant_id', $variant_id[$key] ]
                                        ])->whereDate('created_at', '>=', $start_date)
                                          ->whereDate('created_at', '<=' , $end_date)
                                          ->sum('total');

                                        $product_purchase_data = DB::table('product_purchases')->where([
                                            ['product_id', $pro_id],
                                            ['variant_id', $variant_id[$key] ]
                                        ])->whereDate('created_at','>=', $start_date)
                                          ->whereDate('created_at','<=', $end_date)
                                          ->get();

                                        $sold_price = DB::table('product_sales')->where([
                                            ['product_id', $pro_id],
                                            ['variant_id', $variant_id[$key] ]
                                        ])->whereDate('created_at','>=', $start_date)
                                          ->whereDate('created_at','<=', $end_date)
                                          ->sum('total');

                                        $product_sale_data = DB::table('product_sales')->where([
                                            ['product_id', $pro_id],
                                            ['variant_id', $variant_id[$key] ]
                                        ])->whereDate('created_at','>=', $start_date)
                                          ->whereDate('created_at','<=', $end_date)
                                          ->get();
                                    }
                                    else {
                                        $purchased_cost = DB::table('product_purchases')->where('product_id', $pro_id)->whereDate('created_at', '>=', $start_date)->whereDate('created_at', '<=' , $end_date)->sum('total');

                                        $product_purchase_data = DB::table('product_purchases')->where('product_id', $pro_id)->whereDate('created_at','>=', $start_date)->whereDate('created_at','<=', $end_date)->get();

                                        $sold_price = DB::table('product_sales')->where('product_id', $pro_id)
                                        ->whereDate('created_at','>=', $start_date)->whereDate('created_at','<=', $end_date)->sum('total');

                                        $product_sale_data = DB::table('product_sales')->where('product_id', $pro_id)->whereDate('created_at','>=', $start_date)->whereDate('created_at','<=', $end_date)->get();
                                    }
                                }
                                else{
                                    if($variant_id[$key]) {
                                        $purchased_cost = DB::table('purchases')
                                            ->join('product_purchases', 'purchases.id', '=', 'product_purchases.purchase_id')->where([
                                                ['product_purchases.product_id', $pro_id],
                                                ['product_purchases.variant_id', $variant_id[$key] ],
                                                ['purchases.warehouse_id', $warehouse_id]
                                            ])->whereDate('purchases.created_at','>=', $start_date)->whereDate('purchases.created_at','<=', $end_date)->sum('total');

                                        $product_purchase_data = DB::table('purchases')
                                            ->join('product_purchases', 'purchases.id', '=', 'product_purchases.purchase_id')->where([
                                                ['product_purchases.product_id', $pro_id],
                                                ['product_purchases.variant_id', $variant_id[$key] ],
                                                ['purchases.warehouse_id', $warehouse_id]
                                            ])->whereDate('purchases.created_at','>=', $start_date)->whereDate('purchases.created_at','<=', $end_date)->get();

                                        $sold_price = DB::table('sales')
                                            ->join('product_sales', 'sales.id', '=', 'product_sales.sale_id')->where([
                                                ['product_sales.product_id', $pro_id],
                                                ['variant_id', $variant_id[$key] ],
                                                ['sales.warehouse_id', $warehouse_id]
                                            ])->whereDate('sales.created_at','>=', $start_date)->whereDate('sales.created_at','<=', $end_date)->sum('total');

                                        $product_sale_data = DB::table('sales')
                                            ->join('product_sales', 'sales.id', '=', 'product_sales.sale_id')->where([
                                                ['product_sales.product_id', $pro_id],
                                                ['variant_id', $variant_id[$key] ],
                                                ['sales.warehouse_id', $warehouse_id]
                                            ])->whereDate('sales.created_at','>=', $start_date)->whereDate('sales.created_at','<=', $end_date)->get();
                                    }
                                    else {
                                        $purchased_cost = DB::table('purchases')
                                            ->join('product_purchases', 'purchases.id', '=', 'product_purchases.purchase_id')->where([
                                                ['product_purchases.product_id', $pro_id],
                                                ['purchases.warehouse_id', $warehouse_id]
                                            ])->whereDate('purchases.created_at','>=', $start_date)->whereDate('purchases.created_at','<=', $end_date)->sum('total');

                                        $product_purchase_data = DB::table('purchases')
                                            ->join('product_purchases', 'purchases.id', '=', 'product_purchases.purchase_id')->where([
                                                ['product_purchases.product_id', $pro_id],
                                                ['purchases.warehouse_id', $warehouse_id]
                                            ])->whereDate('purchases.created_at','>=', $start_date)->whereDate('purchases.created_at','<=', $end_date)->get();

                                        $sold_price = DB::table('sales')
                                            ->join('product_sales', 'sales.id', '=', 'product_sales.sale_id')->where([
                                                ['product_sales.product_id', $pro_id],
                                                ['sales.warehouse_id', $warehouse_id]
                                            ])->whereDate('sales.created_at','>=', $start_date)->whereDate('sales.created_at','<=', $end_date)->sum('total');
                                        $product_sale_data = DB::table('sales')
                                            ->join('product_sales', 'sales.id', '=', 'product_sales.sale_id')->where([
                                                ['product_sales.product_id', $pro_id],
                                                ['sales.warehouse_id', $warehouse_id]
                                            ])->whereDate('sales.created_at','>=', $start_date)->whereDate('sales.created_at','<=', $end_date)->get();
                                    }
                                }
                                $purchased_qty = 0;
                                foreach ($product_purchase_data as $product_purchase) {
                                    $unit = DB::table('units')->find($product_purchase->purchase_unit_id);
                                    if($unit->operator == '*'){
                                        $purchased_qty += $product_purchase->qty * $unit->operation_value;
                                    }
                                    elseif($unit->operator == '/'){
                                        $purchased_qty += $product_purchase->qty / $unit->operation_value;
                                    }
                                }

                                $sold_qty = 0;
                                foreach ($product_sale_data as $product_sale) {
                                    $unit = DB::table('units')->find($product_sale->sale_unit_id);
                                    if($unit){
                                        if($unit->operator == '*')
                                            $sold_qty += $product_sale->qty * $unit->operation_value;
                                        elseif($unit->operator == '/')
                                            $sold_qty += $product_sale->qty / $unit->operation_value;
                                    }
                                    else
                                        $sold_qty += $product_sale->qty;
                                }

                                if($purchased_qty > 0)
                                    $profit = $sold_price - (($purchased_cost / $purchased_qty) * $sold_qty);
                                else
                                   $profit =  $sold_price;
                            ?>
                            <td><?php echo e(number_format((float)$purchased_cost, 2, '.', '')); ?></td>
                            <td><?php echo e($purchased_qty); ?></td>
                            <td><?php echo e(number_format((float)$sold_price, 2, '.', '')); ?></td>
                            <td><?php echo e($sold_qty); ?></td>
                            <td><?php echo e(number_format((float)$profit, 2, '.', '')); ?></td>
                            <td><?php echo e($product_qty[$key]); ?></td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <?php endif; ?>
                    </tbody>
                    <tfoot>
                        <th></th>
                        <th>Total</th>
                        <th>0.00</th>
                        <th>0.00</th>
                        <th>0.00</th>
                        <th>0.00</th>
                        <th>0.00</th>
                        <th>0.00</th>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</section>

<script type="text/javascript">

    $("ul#report").siblings('a').attr('aria-expanded','true');
    $("ul#report").addClass("show");
    $("ul#report #product-report-menu").addClass("active");

    $('#warehouse_id').val($('input[name="warehouse_id_hidden"]').val());
    $('.selectpicker').selectpicker('refresh');

    $('#report-table').DataTable( {
        "order": [],
        'language': {
            'lengthMenu': '_MENU_ <?php echo e(trans("file.records per page")); ?>',
             "info":      '<?php echo e(trans("file.Showing")); ?> _START_ - _END_ (_TOTAL_)',
            "search":  '<?php echo e(trans("file.Search")); ?>',
            'paginate': {
                    'previous': '<?php echo e(trans("file.Previous")); ?>',
                    'next': '<?php echo e(trans("file.Next")); ?>'
            }
        },
        'columnDefs': [
            {
                "orderable": false,
                'targets': 0
            },
            {
                'checkboxes': {
                   'selectRow': true
                },
                'targets': 0
            }
        ],
        'select': { style: 'multi',  selector: 'td:first-child'},
        'lengthMenu': [[10, 25, 50, -1], [10, 25, 50, "All"]],
        dom: '<"row"lfB>rtip',
        buttons: [
            {
                extend: 'pdf',
                text: '<?php echo e(trans("file.PDF")); ?>',
                exportOptions: {
                    columns: ':visible:not(.not-exported)',
                    rows: ':visible'
                },
                action: function(e, dt, button, config) {
                    datatable_sum(dt, true);
                    $.fn.dataTable.ext.buttons.pdfHtml5.action.call(this, e, dt, button, config);
                    datatable_sum(dt, false);
                },
                footer:true
            },
            {
                extend: 'csv',
                text: '<?php echo e(trans("file.CSV")); ?>',
                exportOptions: {
                    columns: ':visible:not(.not-exported)',
                    rows: ':visible'
                },
                action: function(e, dt, button, config) {
                    datatable_sum(dt, true);
                    $.fn.dataTable.ext.buttons.csvHtml5.action.call(this, e, dt, button, config);
                    datatable_sum(dt, false);
                },
                footer:true
            },
            {
                extend: 'print',
                text: '<?php echo e(trans("file.Print")); ?>',
                exportOptions: {
                    columns: ':visible:not(.not-exported)',
                    rows: ':visible'
                },
                action: function(e, dt, button, config) {
                    datatable_sum(dt, true);
                    $.fn.dataTable.ext.buttons.print.action.call(this, e, dt, button, config);
                    datatable_sum(dt, false);
                },
                footer:true
            },
            {
                extend: 'colvis',
                text: '<?php echo e(trans("file.Column visibility")); ?>',
                columns: ':gt(0)'
            }
        ],
        drawCallback: function () {
            var api = this.api();
            datatable_sum(api, false);
        }
    } );

    function datatable_sum(dt_selector, is_calling_first) {
        if (dt_selector.rows( '.selected' ).any() && is_calling_first) {
            var rows = dt_selector.rows( '.selected' ).indexes();

            $( dt_selector.column( 2 ).footer() ).html(dt_selector.cells( rows, 2, { page: 'current' } ).data().sum().toFixed(2));
            $( dt_selector.column( 3 ).footer() ).html(dt_selector.cells( rows, 3, { page: 'current' } ).data().sum());
            $( dt_selector.column( 4 ).footer() ).html(dt_selector.cells( rows, 4, { page: 'current' } ).data().sum().toFixed(2));
            $( dt_selector.column( 5 ).footer() ).html(dt_selector.cells( rows, 5, { page: 'current' } ).data().sum());
            $( dt_selector.column( 6 ).footer() ).html(dt_selector.cells( rows, 6, { page: 'current' } ).data().sum().toFixed(2));
            $( dt_selector.column( 7 ).footer() ).html(dt_selector.cells( rows, 7, { page: 'current' } ).data().sum());
        }
        else {
            $( dt_selector.column( 2 ).footer() ).html(dt_selector.column( 2, {page:'current'} ).data().sum().toFixed(2));
            $( dt_selector.column( 3 ).footer() ).html(dt_selector.column( 3, {page:'current'} ).data().sum());
            $( dt_selector.column( 4 ).footer() ).html(dt_selector.column( 4, {page:'current'} ).data().sum().toFixed(2));
            $( dt_selector.column( 5 ).footer() ).html(dt_selector.column( 5, {page:'current'} ).data().sum());
            $( dt_selector.column( 6 ).footer() ).html(dt_selector.column( 6, {page:'current'} ).data().sum().toFixed(2));
            $( dt_selector.column( 7 ).footer() ).html(dt_selector.column( 7, {page:'current'} ).data().sum());
        }
    }


$(".daterangepicker-field").daterangepicker({
  callback: function(startDate, endDate, period){
    var start_date = startDate.format('YYYY-MM-DD');
    var end_date = endDate.format('YYYY-MM-DD');
    var title = start_date + ' To ' + end_date;
    $(this).val(title);
    $('input[name="start_date"]').val(start_date);
    $('input[name="end_date"]').val(end_date);
  }
});

</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layout.main', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>
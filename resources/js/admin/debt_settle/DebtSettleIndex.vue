<template>
    <div class="main-content app-content"> <!-- container -->
        <div class="main-container container-fluid"> <!-- breadcrumb -->
            <div class="breadcrumb-header justify-content-between">
                <div class="justify-content-center mt-2">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item tx-15"><a href="/xadmin/dashboard/index">HOME</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Khách hàng thanh toán</li>
                    </ol>
                </div>
            </div> <!-- /breadcrumb --> <!-- row -->

            <div class="row row-sm">

                <div class="col-xl-12">
                    <div class="card">
                        <div class="card-header pb-0">
                            <div class="d-flex justify-content-between">
                                <h4 class="card-title mg-b-0">Khách hàng thanh toán</h4></div>
                        </div>
                        <div class="card-body">
                            <div class="card-header border-0 pt-6">
<!--                                <div class="d-flex align-items-center position-relative my-1">-->
<!--                                    <input type="text" @keydown.enter="doFilter('keyword', filter.keyword, $event)"-->
<!--                                           v-model="filter.keyword" placeholder="Tìm kiếm..."-->
<!--                                           class="form-control col-lg-4"/>-->
<!--                                </div>-->
                                <div class="card-toolbar d-flex justify-content-end">
                                        <a href="/xadmin/debt_settle/create" class="btn btn-primary "
                                           style="margin-left: 10px" target="_blank"><i class="fa fa-plus"/> Thêm</a>
                                </div>
                                <form class="col-lg-12" style="margin-top: 20px">
                                    <div class="row">
                                        <div class="form-group col-lg-4">
                                            <label>Chọn khách hàng</label>
                                            <select class="js-example-responsive" style="width: 100%" v-model="filter.customer_id">
                                                <option value="0">All</option>
                                                <option v-for="customer in customers" :value="customer.id">{{customer.id}}-{{customer.name}}</option>
                                            </select>

                                        </div>
                                        <div class="form-group col-lg-2">
                                            <label>Năm lập báo cáo</label>
                                            <select required class="form-control form-select" v-model="filter.year">
                                                <option value="" disabled selected>Chọn năm</option>
                                                <option value="0">All</option>
                                                <option v-for="year in years" :value="year">{{year}}</option>
                                            </select>
                                        </div>
                                        <div class="form-group col-lg-2">
                                            <label>Tháng lập báo cáo </label>
                                            <select class="form-control" v-model="filter.month" required>
                                                <option value="" disabled selected>Chọn tháng</option>
                                                <option value="0">All</option>
                                                <option v-for="month in months" :value="month">{{month}}</option>
                                            </select>
                                        </div>
                                        <div class="form-group col-lg-4">
                                            <label>Chọn ngày tháng </label>
                                            <Daterangepicker v-model="filter.created" class="active"
                                                             placeholder="Creation date" readonly></Daterangepicker>
                                            <span v-if="filter.created!==''"
                                                  class="svg-icon svg-icon-2 svg-icon-lg-1 me-0" @click="filterClear">
                                            <svg type="button" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                 viewBox="0 0 24 24" fill="none"
                                                 style="float: right;margin: -32px 3px 0px;">
                                            <rect opacity="0.5" x="6" y="17.3137" width="16" height="2" rx="1"
                                                  transform="rotate(-45 6 17.3137)" fill="black" style="fill:red"/>
                                                        <rect x="7.41422" y="6" width="16" height="2" rx="1"
                                                              transform="rotate(45 7.41422 6)" fill="black"
                                                              style="fill:red"/>
                                            </svg>
                                            </span>
                                        </div>
                                        <div style="margin-top: 28px">
                                            <button type="button" class="btn btn-primary" @click="doFilter()">Search
                                            </button>
                                        </div>
                                    </div>
                                </form>

                            </div>
                            <div class="table-responsive">
                                <table class="table mg-b-0 text-md-nowrap">
                                    <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Customer</th>
                                        <th>Số tiền</th>
                                        <th>Chi phí ngoài</th>
                                        <th>Tháng/năm</th>
                                        <th>Thời gian</th>
                                        <th>Ngày tạo</th>
                                        <th>Action</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr v-for="(entry,index) in entries">
                                        <td >{{entry.id}}</td>
                                        <td v-text="entry.customer_id + ' - ' + entry.customer_name"></td>
                                        <td v-text="entry.pay_debt"></td>
                                        <td v-text="entry.cost_incurr"></td>
                                        <td v-text="entry.month + ' / ' + entry.year"></td>
                                        <td>{{d(entry.due_date)}}</td>
                                        <td>{{d(entry.created_at)}}</td>
                                        <td class="">
                                            <a :href="'/xadmin/debt_settle/edit?id='+entry.id" class="btn " target="_blank"><i
                                                    class="fa fa-edit"></i></a>
                                            <a @click="remove(entry)" href="javascript:;" class="btn "><i
                                                    class="fa fa-trash"></i></a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Tổng</td>
                                        <td></td>
                                        <td v-text="totalPayDebt"></td>
                                        <td v-text="totalCost"></td>
                                        <td></td>
                                        <td>
                                        </td>
                                        <td></td>
                                    </tr>
                                    </tbody>
                                </table>
                                <div class="float-right" style="margin-top:10px; ">
<!--                                    <Paginate :value="paginate" :pagechange="onPageChange"></Paginate>-->
                                </div>
                            </div>
                        </div>
                    </div>
                </div> <!--/div--> <!--div-->

            </div> <!-- /row -->
        </div>


    </div> <!-- /main-content -->

</template>

<script>
import {$get, $post, getTimeRangeAll, getTimeThisMonth} from "../../utils";
    import $router from '../../lib/SimpleRouter';
    import ActionBar from '../../components/ActionBar';


    let created = getTimeThisMonth();
    const $q = $router.getQuery();
    export default {
        name: "DebtSettleIndex.vue",
        components: {ActionBar},
        data() {
            let filter = {
                created: $q.created || '',
                year: $q.year || '',
                month: $q.month || '',
                customer_id: $q.customer_id || ''
            }
            let isShowFilter = false;
            for (var key in filter) {
                if (filter[key] != '') {
                    isShowFilter = true;
                }
            }
            return {
                isShowFilter,
                customers: $json.customer || [],
                years: [],
                months: [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12],
                totalCost:'',
                totalPayDebt:'',
                entries: [],
                filter
                // paginate: {
                //     currentPage: 1,
                //     lastPage: 1
                // }
            }
        },
        mounted() {
            let numberYear = moment().year() - 2000;
            for (let i = 0; i < numberYear; i++) {
                this.years.push(moment().year() - i);
            }
            const vm = this;
            $(".js-example-responsive").select2({
                placeholder: "All"
            }).on("change", function(e) {
                vm.filter.customer_id = $(this).val();
            });
            $router.on('/', this.load).init();
        },
        methods: {
            async load() {
                let query = $router.getQuery();
                const res = await $get('/xadmin/debt_settle/dataCustomer', query);
                // this.paginate = res.paginate;
                this.entries = res.data;
                this.totalCost=this.entries.reduce((accumulator, currentValue)=>{
                    return accumulator + parseInt(currentValue['cost_incurr']);
                },0);
                this.totalPayDebt=this.entries.reduce((accumulator, currentValue)=>{
                    return accumulator + parseInt(currentValue['pay_debt']);
                },0);

                this.totalCost=parseFloat(this.totalCost).toLocaleString('en-US');

                this.totalPayDebt=parseFloat(this.totalPayDebt).toLocaleString('en-US');
                for (let item of this.entries) {
                    if (item.cost_incurr || item.cost_incurr == 0) {
                        item.cost_incurr = parseFloat(item.cost_incurr).toLocaleString('en-US');
                    }
                    if (item.pay_debt || item.pay_debt == 0) {
                        item.pay_debt = parseFloat(item.pay_debt).toLocaleString('en-US');
                    }
                }

            },
            async remove(entry) {
                if (!confirm('Xóa bản ghi: ' + entry.id)) {
                    return;
                }

                const res = await $post('/xadmin/debt_settle/remove', {id: entry.id});

                if (res.code) {
                    toastr.error(res.message);
                } else {
                    toastr.success(res.message);
                }

                $router.updateQuery({page: this.paginate.currentPage, _: Date.now()});
            },
            filterClear() {
                for (var key in this.filter) {
                    this.filter[key] = '';
                }
                $router.setQuery({});
            },
            doFilter() {
                $router.setQuery(this.filter)
            },
            async toggleStatus(entry) {
                const res = await $post('/xadmin/debt_settle/toggleStatus', {
                    id: entry.id,
                    status: entry.status
                });

                if (res.code === 200) {
                    toastr.success(res.message);
                } else {
                    toastr.error(res.message);
                }
            },
            onPageChange(page) {
                $router.updateQuery({page: page})
            }
        }
    }
</script>

<style scoped>
select:required:invalid {
    color: #adadad;
}

option[value=""][disabled] {
    display: none;
}

option {
    color: black;
}
</style>

<template>
    <div>trung</div>
    <div class="main-content app-content"> <!-- container -->
        <div class="main-container container-fluid"> <!-- breadcrumb -->
            <div class="breadcrumb-header justify-content-between">
                <div class="justify-content-center mt-2">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item tx-15"><a href="/xadmin/dashboard/index">HOME</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Thông kê</li>
                    </ol>
                </div>
            </div> <!-- /breadcrumb --> <!-- row -->

            <div class="row row-sm">

                <div class="col-xl-12">
                    <div class="card">
                        <div class="card-header pb-0">
                            <div class="d-flex justify-content-between">
                                <h4 class="card-title mg-b-0">Thống kê công nợ</h4></div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-xl-8">
                                    <form class="form-inline">
<!--                                        <div class="form-group mx-sm-3 mb-2">-->
<!--                                            <input @keydown.enter="doFilter('keyword', filter.keyword, $event)" v-model="filter.keyword"-->
<!--                                                   type="text"-->
<!--                                                   class="form-control" placeholder="tìm kiếm" >-->
<!--                                        </div>-->
<!--                                        <div class="form-group mx-sm-3 mb-2">-->
<!--                                            <Daterangepicker-->
<!--                                                @update:modelValue="(value) => doFilter('created', value, $event)"-->
<!--                                                v-model="filter.created" placeholder="Ngày tạo"></Daterangepicker>-->
<!--                                        </div>-->
                                        <div class="form-group mx-sm-3 mb-2">
                                            <span style="margin-right: 10px">Chọn năm</span>
                                            <select class="form-control" v-model="filter.year">
                                                <option value="">All</option>
                                                <option v-for="year in years" :value="year" v-text="year"></option>
                                            </select>
                                        </div>
                                        <div class="form-group mx-sm-3 mb-2">
                                            <span style="margin-right: 10px">Sale</span>
                                            <select class="form-control">
                                                <option value="0">All</option>
                                            </select>
                                        </div>

                                        <div class="form-group mx-sm-3 mb-2">
                                            <button @click="filterClear()" type="button"
                                                    class="btn btn-light">Xóa
                                            </button>
                                        </div>

                                    </form>
                                </div>
                            </div>
                            <div class="row" style="position:relative;left:32px;top:-15px">
                                <button type="button" class="btn btn-primary" @click="doFilter()">Search</button>
                            </div>
                            <div class="table-responsive">
                                <table class="table mg-b-0 text-md-nowrap">
                                    <thead>
                                    <tr>
                                        <th>Tháng</th>
                                        <th>Cần thu</th>
                                        <th>Đã thu</th>
                                        <th>Còn nợ</th>
                                        <th></th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr v-for="(entry,index) in entries">
                                        <td v-text="entry.month"></td>
                                        <td v-text="entry.bookingTotal"></td>
                                        <td v-text="entry.debtTotal"></td>
                                        <td v-text="entry.owe"></td>
                                        <td class="">
                                            <a :href="'/xadmin/debt_settle/payDetail?year='+filter.year +'&month='+entry.month" class="btn " target="_blank">Chi tiết</a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Tổng</td>
                                        <td v-text="payBooking"></td>
                                        <td v-text="payDebt"></td>
                                        <td v-text="totalOwe"></td>
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
    import {$get, $post, getTimeRangeAll} from "../../utils";
    import $router from '../../lib/SimpleRouter';
    import ActionBar from '../../components/ActionBar';


    // let created = getTimeRangeAll();
    const $q = $router.getQuery();
    export default {
        name: "DebtSettleIndex.vue",
        components: {ActionBar},
        data() {
            let date = new Date();
            let year = date.getFullYear();
            return {
                customers: $json.customer || [],
                years: [2021, 2022, 2023, 2024, 2025, 2026, 2027, 2028, 2029, 2030, 2031, 2032, 2033, 2034, 2035, 2036, 2037, 2038, 2039],
                months: [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12],
                totalOwe:'',
                payBooking:'',
                payDebt:'',
                entries: [],
                filter: {
                    keyword: $q.keyword || '',
                    // created: $q.created || created,
                    year: $q.year || year,
                },
                // paginate: {
                //     currentPage: 1,
                //     lastPage: 1
                // }
            }
        },
        mounted() {
            const vm = this;
            $(".js-example-responsive").select2({
                placeholder: "All"
            }).on("change", function(e) {
                vm.filter.customer = $(this).val();
            });
            $router.on('/', this.load).init();
        },
        methods: {
            async load() {
                this.doFilter();
                let query = $router.getQuery();
                const res = await $get('/xadmin/debt_settle/dataStatistical', query);
                // this.paginate = res.paginate;
                this.entries = res.data;
                this.payBooking=this.entries.reduce((accumulator, currentValue)=>{
                    return accumulator + parseInt(currentValue['bookingTotal']);
                },0);
                this.payDebt=this.entries.reduce((accumulator, currentValue)=>{
                    return accumulator + parseInt(currentValue['debtTotal']);
                },0);
                this.totalOwe=(this.payBooking)-(this.payDebt);
                this.totalOwe=parseFloat(this.totalOwe).toLocaleString('en-US');
                this.payBooking=parseFloat(this.payBooking).toLocaleString('en-US');

                this.payDebt=parseFloat(this.payDebt).toLocaleString('en-US');
                for (let item of this.entries) {
                    let owe=(item.bookingTotal)-(item.debtTotal)
                    if (item.bookingTotal || item.debtTotal==0) {
                        item.bookingTotal = parseFloat(item.bookingTotal).toLocaleString('en-US');
                    }
                    if (item.debtTotal || item.debtTotal == 0) {
                        item.debtTotal = parseFloat(item.debtTotal).toLocaleString('en-US');
                    }
                    if (owe || owe==0) {
                        item.owe = parseFloat(owe).toLocaleString('en-US');
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

</style>

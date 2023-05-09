
<template>
    <div class="main-content app-content"> <!-- container -->
        <ActionBar label="Lưu lại" @action="save()" backUrl="/xadmin/debt_settle/index"/>
        <div class="main-container container-fluid"> <!-- breadcrumb -->
            <div class="breadcrumb-header justify-content-between">
                <div class="justify-content-center mt-2">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item tx-15"><a href="/xadmin/dashboard/index">HOME</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Thông tin thanh toán</li>
                    </ol>
                </div>
            </div> <!-- /breadcrumb --> <!-- row -->

            <div class="row row-sm">

                <div class="col-xl-12">
                    <div class="card">
                        <div class="card-header pb-0">
                            <div class="d-flex justify-content-between">
                                <h4 v-if="entry.id" class="card-title mg-b-0">Chỉnh sửa công nợ</h4>
                                <h4 v-else class="card-title mg-b-0">Tạo mới công nợ</h4>
                            </div>
                        </div>
                        <div class="card-body">
                            <input v-model="entry.id" type="hidden" name="id">
                            <div class="form-group">
                                <label>Customer</label>
                                <select class="js-example-responsive" style="width: 100%" v-model="entry.customer_id">
                                    <option v-for="customer in entries" :value="customer.id">
                                        {{ customer.id }}-{{ customer.name }}
                                    </option>
                                </select>
                                <error-label for="f_customer_id" :errors="errors.customer_id"></error-label>
                            </div>
                            <div class="row">
                                <div class="form-group col-lg-6">
                                    <label>Tháng lập công nợ</label>
                                    <select class="form-control" v-model="entry.month" required>
                                        <option value="" disabled selected>Chọn tháng</option>
                                        <option v-for="month in months" :value="month">{{ month }}</option>
                                    </select>
                                </div>
                                <div class="form-group col-lg-6">
                                    <label>Năm lập công nợ</label>
                                    <select class="form-control" v-model="entry.year" required>
                                        <option value="" disabled selected>Chọn năm</option>
                                        <option v-for="year in years" :value="year">{{ year }}</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Số tiền</label>
                                <input class="form-control" type="text" v-model="payBooking"
                                       @input="payBookingValue()"/>
                            </div>
                            <div class="form-group col-lg-12">
                                <label>Note</label>
                                <br>
                                <textarea class="form-control" v-model="entry.note_debt" placeholder="Nhập ghi chú"></textarea>
                                <error-label for="f_status" :errors="errors.note"></error-label>
                            </div>
                        </div>
                    </div>
                </div> <!--/div--> <!--div-->

            </div> <!-- /row -->
        </div>
    </div>

</template>

<script>
import {$get, $post} from "../../utils";
import ActionBar from '../../components/ActionBar';
import $router from "../../lib/SimpleRouter";
import RichtextEditor from "../../components/RichtextEditor";
import Daterangepicker from "../../components/Daterangepicker.vue";
import Datepicker from "../../components/Datepicker.vue";


export default {
    name: "DebtForm.vue",
    components: {Datepicker, Daterangepicker, ActionBar,RichtextEditor},
    data() {
        let entry
        let payBooking
        if($json.entry)
        {
            entry = $json.entry
            payBooking = entry.pay_booking || 0
        }
        else {
            entry={
                pay_booking: 0,
                year: '',
                month: '',
            }
            payBooking = 0


        }
        return {
            month:'',
            months: [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12],
            year: '',
            years: [],
            payBooking,
            entries:[],
            entry: entry,
            isLoading: false,
            errors: {}
        }
    },
    mounted() {
        let numberYear = moment().year() - 2000;
        for (let i = 0; i < numberYear; i++) {
            this.years.push(moment().year() - i);
        }
        const vm = this;
        $(".js-example-responsive").select2({
        }).on("change", function(e) {
            vm.entry.customer_id = $(this).val();
        });
        $router.on('/', this.load).init();
    },
    methods: {
        payBookingValue() {
            {
                this.entry.pay_booking = this.payBooking.replace(/[^0-9.-]+/g, '') || 0;
                if (this.entry.pay_booking === '') {
                    this.payBooking = '0';
                }
                else {
                    this.payBooking = parseFloat(this.entry.pay_booking).toLocaleString('en-US');
                }
            }

        },
        async load() {
            let query = $router.getQuery();
            const res = await $get('/xadmin/debt_settle/dataCreate', query);
            this.entries = res.customers;
        },
        async save() {
            this.isLoading = true;
            const res = await $post('/xadmin/debt_settle/save', {entry: this.entry});
            this.isLoading = false;
            if (res.errors) {
                this.errors = res.errors;
                return;
            }
            if (res.code) {
                toastr.error(res.message);
            } else {
                this.errors = {};
                toastr.success(res.message);

                if (!this.entry.id) {
                    location.replace('/xadmin/debt_settle/debtCreate');
                }
            }
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

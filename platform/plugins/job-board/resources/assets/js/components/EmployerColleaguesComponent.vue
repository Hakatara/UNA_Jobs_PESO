<template>
    <div>
        <div class="form-group" v-for="(item, index) in items">
            <div class="input-group mb-3">
                <input type="email" name="employer_colleagues[]" v-model="item.value" class="form-control"
                       :placeholder="__('Email')">
                <div class="input-group-append">
                    <button class="btn btn-warning" type="button" @click="deleteRow(index)"><i class="fa fa-times"></i>
                    </button>
                </div>
            </div>
        </div>
        <div class="form-group">
            <button class="btn btn-info" type="button" @click="addRow">{{ __('Add new') }}</button>
        </div>
    </div>
</template>

<script>
export default {
    data: function () {
        return {
            items: [{value: ''}]
        };
    },
    mounted() {
        let data = this.data;
        if (data.length) {
            this.items = [];
            for (const item of data) {
                this.items.push({value: item});
            }
        }
    },
    props: {
        data: {
            type: Array,
            default: () => null,
        }
    },

    methods: {
        addRow: function () {
            this.items.push({value: ''});
        },
        deleteRow: function (index) {
            this.items.splice(index, 1);
        }
    }
}
</script>

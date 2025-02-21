<template>
    <div>
        <p class="control" v-for="dataset in $parent.$parent.datasets" :key="prefix+'ds_'+dataset.id" style="margin:0px">
            <input
              class="is-checkradio is-small"
              :id="prefix+'ds_'+dataset.id"
              type="checkbox"
              :name="prefix+'ds_'+dataset.id"
              :value="dataset.internalident"
              v-model="selected"
              :alt="dataset.description"
            />
            <label :for="prefix+'ds_'+dataset.id" style="white-space: nowrap">{{ dataset.name }}</label>
          </p>
    </div>
</template>
<script>
export default {
    props:['lang','selectedDatasets'],
    data() {
        return {
            selected:[],
            prefix:""
        }
    },
    created: function() {
        this.selected = this.selectedDatasets;
        for (var i = 0; i < 8; i++) {
            this.prefix += String.fromCharCode(97 + Math.floor(Math.random() * 26))
        }
        // prefix is used to make name and id of the input-fields unique, so that when component is used multiple time you get no conflicts
    },
    watch: {
        selected: function(v) {
            this.$emit('change', v);
        },
        selectedDatasets: function(v) {
            this.selected = v;
        }
    }
}
</script>
<style>
</style>
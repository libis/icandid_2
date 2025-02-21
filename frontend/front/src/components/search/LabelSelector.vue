<template>
    <div>
        <p class="control" v-for="label in $parent.$parent.labels" :key="prefix+'lbl_'+label.id" style="margin:0px">
          <input
            class="is-checkradio is-small"
            :id="prefix+'lbl'+label.id"
            type="checkbox"
            :name="prefix+'lbl_'+label.id"
            :value="label.id"
            v-model="selected"
          />
          <label :for="prefix+'lbl'+label.id" style="white-space: nowrap">{{ label['name_'+$ml.current]}}</label>
        </p>  
    </div>
</template>
<script>
export default {
    props:['lang','selectedLabels'],
    data() {
        return {
            selected:[],
            prefix:""
        }
    },
    created: function() {
        this.selected = this.selectedLabels;
        for (var i = 0; i < 8; i++) {
            this.prefix += String.fromCharCode(97 + Math.floor(Math.random() * 26))
        }
        // prefix is used to make name and id of the input-fields unique, so that when component is used multiple time you get no conflicts
    },
    watch: {
        selected: function(v) {
            this.$emit('change', v)
        }
    }
}
</script>
<style>
</style>
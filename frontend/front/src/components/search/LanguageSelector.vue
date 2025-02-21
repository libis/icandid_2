<template>
<div class="columns" style="padding-left:2em" v-if="$parent.$parent.languages.length > 1">
    <div class="column">
        <p class="control" v-for="language in $parent.$parent.languages" :key="prefix+'lang_'+language.id" style="margin:0px">
            <input
              class="is-checkradio is-small"
              :id="prefix+'lang_'+language.id"
              type="checkbox"
              :name="prefix+'lang_'+language.id"
              :value="language.id"
              v-model="selected"
              style="border: 1px solid red"
            />
            <label :for="prefix+'lang_'+language.id" style="white-space: nowrap">{{ language['name_'+$ml.current]}}</label>
          </p>
    </div>
</div>    
</template>
<script>
export default {
    props:['lang','selectedLanguages'],
    data() {
        return {
            selected:[],
            prefix:""
        }
    },
    created: function() {
        this.selected = this.selectedLanguages;
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
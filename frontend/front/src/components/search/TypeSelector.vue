<template>
<div class="tabs is-toggle is-small" style="margin:0px">
      <ul>
        <li v-bind:class="{'is-active': (selected=='all')}">
          <a @click.prevent.stop="selected='all'">{{ $ml.get('allthewords') }}</a>
        </li>
        <li v-bind:class="{'is-active': (selected=='one')}">
          <a @click.prevent.stop="selected='one'">{{ $ml.get('oneofwords') }}</a>
        </li>
        <li v-bind:class="{'is-active': (selected=='phrase')}">
          <a @click.prevent.stop="selected='phrase'">{{ $ml.get('exactsentence') }}</a>
        </li>
      </ul>
    </div>    
</template>
<script>
export default {
    props:['lang','selectedType'],
    data() {
        return {
            selected:""
        }
    },
    watch: {
        selected: function(e) {
            this.$emit('change', e)
        }
    },
    created: function() {
        this.selected = this.selectedType;
    }
}
</script>
<style>
</style>
<template>
  <div class="tabs is-toggle is-small" style="overflow-x:scroll;width:fit-content;margin-top:20px;margin-left:30px">
    <span class="vertalign">{{ $ml.get('from') }} : </span><input type="date" class="input" style="width:170px" v-model="from" name="dp_fromdate" />&nbsp;
    <span class="vertalign">{{ $ml.get('toandincluding') }} : </span><input type="date" class="input" style="width:170px" v-model="until" name="dp_todate" />
  </div>    
</template>
<script>
export default {
    props:['lang','selectedPeriod'],
    data() {
        return {
            selected:"",
            from:"",
            until:""
        }
    },
    watch: {
        lang: function() {
            this.info.content = this.$ml.get('periodinfotooltip');
        },
        from: function() {
            this.selected = this.from + " " + this.until;
            this.$emit('change', this.selected)
        },
        until: function() {
            this.selected = this.from + " " + this.until;
            this.$emit('change', this.selected)
        }

    },
    created: function() {
        this.selected = this.selectedPeriod;
    }
}  
</script>
<style>
.bar {
  display:inline-block;
}
.element {
  white-space:nowrap;
}


.tooltip {
  display: block !important;
  z-index: 10000;
}

.tooltip .tooltip-inner {
  background: black;
  color: white;
  border-radius: 8px;
  padding: 5px 10px 4px;
  font-size:12px;
  line-height: 120%;
  width:200px

}

.tooltip .tooltip-arrow {
  width: 0;
  height: 0;
  border-style: solid;
  position: absolute;
  margin: 5px;
  border-color: black;
  z-index: 1;
}

.tooltip[x-placement^="top"] {
  margin-bottom: 5px;
}

.tooltip[x-placement^="top"] .tooltip-arrow {
  border-width: 5px 5px 0 5px;
  border-left-color: transparent !important;
  border-right-color: transparent !important;
  border-bottom-color: transparent !important;
  bottom: -5px;
  left: calc(50% - 5px);
  margin-top: 0;
  margin-bottom: 0;
}

.tooltip[x-placement^="bottom"] {
  margin-top: 5px;
}

.tooltip[x-placement^="bottom"] .tooltip-arrow {
  border-width: 0 5px 5px 5px;
  border-left-color: transparent !important;
  border-right-color: transparent !important;
  border-top-color: transparent !important;
  top: -5px;
  left: calc(50% - 5px);
  margin-top: 0;
  margin-bottom: 0;
}

.tooltip[x-placement^="right"] {
  margin-left: 5px;
}

.tooltip[x-placement^="right"] .tooltip-arrow {
  border-width: 5px 5px 5px 0;
  border-left-color: transparent !important;
  border-top-color: transparent !important;
  border-bottom-color: transparent !important;
  left: -5px;
  top: calc(50% - 5px);
  margin-left: 0;
  margin-right: 0;
}

.tooltip[x-placement^="left"] {
  margin-right: 5px;
}

.tooltip[x-placement^="left"] .tooltip-arrow {
  border-width: 5px 0 5px 5px;
  border-top-color: transparent !important;
  border-right-color: transparent !important;
  border-bottom-color: transparent !important;
  right: -5px;
  top: calc(50% - 5px);
  margin-left: 0;
  margin-right: 0;
}

.tooltip.popover .popover-inner {
  background: #f9f9f9;
  color: black;
  padding: 24px;
  border-radius: 5px;
  box-shadow: 0 5px 30px rgba(black, .1);
}

.tooltip.popover .popover-arrow {
  border-color: #f9f9f9;
}

.tooltip[aria-hidden='true'] {
  visibility: hidden;
  opacity: 0;
  transition: opacity .15s, visibility .15s;
}

.tooltip[aria-hidden='false'] {
  visibility: visible;
  opacity: 1;
  transition: opacity .15s;
}
</style>
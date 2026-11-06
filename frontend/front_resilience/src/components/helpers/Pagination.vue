<template>
    <div style="text-align: center;">
        <span v-if="getPage != 1" class="pagenumber" @click="setPage(1)">&lt;&lt;</span><span v-if="getPage != 1" class="pagenumber" @click="down()">&lt;</span>
        <span v-for="number in pages"  :key="number" >
            <span class="pagenumber" :class="{ current: (number == getPage) }" v-if="typeof number == 'number'" @click="setPage(number)">{{ number }}</span>
            <span class="dots" v-else>...</span>
        </span>
        <span v-if="getPage != maxPages" class="pagenumber" @click="up()">&gt;</span><span v-if="getPage != maxPages" class="pagenumber" @click="setPage(maxPages)">&gt;&gt;</span>
    </div>
</template>
<script>
import { mapActions, mapGetters } from "vuex";
export default {
  data() {
    return {}
  },
  props:['listLength','pageSize','scope'],
  computed:{
    ...mapGetters(['getPage']),
    maxPages: function() {
        return Math.ceil(this.listLength / this.pageSize)
    },
    pages: function() {
        var pagenumbers = []
        if ((this.getPage-this.scope) > 1) { pagenumbers.push("...") }

        if (this.getPage <= this.scope) {
            for (let i=1;i<=Math.min(this.getPage+this.scope,this.maxPages);i++) {
                pagenumbers.push(i)
            }
        } else {
            for (let i=this.getPage-this.scope;i<=Math.min(this.getPage+this.scope,this.maxPages);i++) {
                pagenumbers.push(i)
            }
        }

        if ((this.getPage + this.scope) < this.maxPages) {
                pagenumbers.push("...")
        }
        return pagenumbers
    }
  },
  methods: {
    ...mapActions(['setPage']),
    up: function () {
        if (this.getPage != this.maxPages) {
            this.setPage(this.getPage + 1)
        }
    },
    down: function () {
        if (this.getPage > 1) {
            this.setPage(this.getPage - 1)
        }
    }
  }
}
</script>
<style>
.pagenumber {
    padding:8px;
    cursor:pointer;
    font-size:12px
}
.current {
    border:2px solid #052CD2;
    border-radius:3px
}
.dots {
    padding:8px
}
</style>
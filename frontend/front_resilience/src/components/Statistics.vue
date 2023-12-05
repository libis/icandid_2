<template>
  <div class="block">
    <h2 class="title is-2">{{ $ml.get('statistics') }}</h2>
    <Counter ref="counter" ></Counter>
    <div class="columns">
      <div class="column is-half">
        <ProviderPie ref="providerpie" :lang="$ml.current" style="height:850px"/>
      </div>
      <div class="column is-half">
        <TypePie ref="typepie" :lang="$ml.current" style="height:850px"/>
      </div>
    </div>


      <!--
      <div class="column is-two-thirds">
        <RecordsDayBar ref="recordsdaybar" :lang="$ml.current"/>
      </div>
    </div>
    <div class="columns">
      <div class="column is-two-thirds">
        <ArticleLine ref="articleline" :lang="$ml.current"/>
      </div>
    </div>
    <div class="columns"  v-if="isLocalhost">
      <div class="column">
        <NERModels :url="getApiQueryUrl" ref="nermodelsbar" :lang="$ml.current"></NERModels> 
      </div>
    </div>
  -->
    
    
    <!--
    <Articles class="box " :url="getApiQueryUrl" ref="articles"></Articles>
   -->
  </div>
</template>
<script>
import Counter from "./statistics/Counter";
//import NewspaperPie from "./statistics/NewspaperPie";
import ProviderPie from "./statistics/ProviderPie";
//import RecordsDayBar from "./statistics/RecordsDayBar";
//import ArticleLine from "./statistics/ArticleLine";
import TypePie from "./statistics/TypePie";
//import Articles from "./statistics/Articles"
//import NERModels from "./statistics/NERModels"
import { mapGetters } from 'vuex';
export default {
  data() {
    return {
      isLocalhost:(location.host=='localhost:8080'),
    }
  },
  computed: mapGetters(['getApiQueryUrl']),
  components: {
    Counter,
//    NewspaperPie,
    ProviderPie,
//    RecordsDayBar,
//    ArticleLine,
    TypePie,
//    Articles,
//    NERModels
    },
  methods: {
    updateComponents() {
      Object.values(this.$refs).forEach(function(el) {
        try {
          el.update();
        } catch (err) {
          console.log(err);
        }
      });
    }
  },
  watch: {
		// Call the method again if the route changes
		'$route': function() { if (this.$route.name == 'Statistics') this.updateComponents() }
	}
 };
</script>
<style>
canvas {
  position:relative
}
</style>
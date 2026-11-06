<template>
  <div class="gallery">
    <div v-for="collection in getRandomCollections" :key="collection.id" class="collection">
      <div class="collection_panel">
        <div><img class="collection_img" :src="collection.imageurl"></div>
        <p class="collection_name">{{ collection.name }}</p>
        <p class="collection_provider">{{ collection.provider }}</p>
      </div>
      <div class="collection_button">
        <button class="button is-primary" style="background-color: #E5EEFF; border-color: #E5EEFF;color:#001A72;font-size:14px;margin:10px;height:30px;width:50%" @click="$parent.searchFilter('collection',collection.name)" ><i class="fa fa-search" style="margin-right:5px"></i>{{ $ml.get('explorecollection') }}</button>
      </div>
    </div>
  </div>
</template>
<script>
import axios from "axios";
import { mapGetters } from "vuex";
axios.defaults.withCredentials = true;
export default {
  components: {
  },
  props: { count: Number, gridwidth: Number },
  data() {
    return {
      data: []
    }
  },
  computed: {
    ...mapGetters(['getApiCollectionUrl']),
    getRandomCollections() {
      let clonedData = JSON.parse(JSON.stringify(this.data));
      let shuffled = clonedData.sort(() => 0.5 - Math.random());
      return shuffled.slice(0, this.count);
    }
  },
  created() {
    axios
      .get(this.getApiCollectionUrl + "/all")
      .then(res => {
        this.data = res.data
      });
  }
}
</script>
<style scoped>
.collection {
  display: grid;
  grid-template-rows: auto auto;
}
.collection_button {
  align-self:end; 
}
.collection_img {
  border-radius: 10px 10px 0 0;
  width: 100%;
}

.collection_name {
  font-weight: bold;
}
.collection_panel {
  align-self: start;
}

.collection_panel div {
  height:238px;
  overflow:hidden
}
.collection_provider {
  font-style: normal;
}

.gallery {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
  gap: 16px;
  /* spacing between images */
}

.gallery div {
  width: 100%;
  object-fit: cover;
  /* keeps images nicely cropped */
  
  background: #fafafa;
  /* placeholder background */
  border-radius: 10px;
}

.gallery div p {
  margin-left: 8px;
  margin-right: 8px;
  margin-top: 0px;
  margin-bottom: 0px;
}
</style>

<template>
  <div class="content">
    <h1 class="title">{{ $ml.get('savedsearches') }}</h1>
    <SearchesList></SearchesList>
  </div>
</template>
<script>
import SearchesList from "./profile/SearchesList.vue";
export default {
  components: {
    SearchesList
  }
};
</script>
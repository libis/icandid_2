<template>
  <div class="content">
    <h1 class="title">{{ $ml.get('searchhistory') }}</h1>
    <HistoryList></HistoryList>
  </div>
</template>
<script>
import HistoryList from "./profile/HistoryList.vue";
export default {
  components: {
    HistoryList
  }
};
</script>
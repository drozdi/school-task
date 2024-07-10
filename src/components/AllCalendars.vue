<template>
  <v-container>
      <v-btn v-for="(item, index) in items" :key="index" :to="{name:'ep-event', params: {id: item.id}}">
        {{item.name}}
      </v-btn>
  </v-container>
</template>

<script>
export default {
  name: "AllCalendars",
  data: () => ({
    items: []
  }),
  methods: {
    load () {
      const formData = new FormData();
      formData.append('f', "list");
      return this.$http.post('/event/event.php', formData).then((function (res) {
        this.items = res;
      }).bind(this))
    }
  },
  mounted() {
    this.load();
  }
}
</script>

<style scoped>

</style>
<template>
  <v-card color="grey lighten-4" min-width="350px" flat>
    <v-toolbar :color="event.color" dark>
      <v-toolbar-title v-html="event.name + ' ' + fStart"></v-toolbar-title>
      <v-spacer></v-spacer>
      <v-btn icon @click="selectedOpen = false">
        <v-icon>mdi-close</v-icon>
      </v-btn>
    </v-toolbar>
    <br />
    <v-card-title v-html="event.theme"></v-card-title>
    <v-card-subtitle>
      <v-row>
        <v-col cols="2">
          Учитель:
        </v-col>
        <v-col cols="10" v-html="event.teacher"></v-col>
      </v-row>
      <v-row>
        <v-col cols="2">
          Адресс почты:
        </v-col>
        <v-col cols="10">
          <a :href="'mailto:'+event.email">{{event.email}}</a>
        </v-col>
      </v-row>
    </v-card-subtitle>
    <v-card-text>
      <p v-if="event.ht"><b>Домашнее задание:</b><br />{{event.ht}}</p>
      <p v-if="event.des">
        <b>Комментарий к заданию:</b><br />
        <span v-html="event.des"></span>
      </p>
      <p v-if="event.pt"><b>Практическое задание:</b><br />{{event.pt}}</p>
      <template v-if="event.net && event.net.length">
        <hr />
        <p>
          <b>Интернет-ресурсы:</b><br />
          <span v-for="(link, index) in event.net" :key="index">
            <a :href="link" target="_blank">{{link}}</a><br />
          </span>
        </p>
      </template>
      <template v-if="event.files">
        <hr />
        <a v-for="(link, name) in event.files" :key="name" :href="link" target="_blank">{{name}}</a>
      </template>
    </v-card-text>
  </v-card>
</template>

<script>
export default {
  name: "EpEvent",
  model: {
    prop: 'event'
  },
  props: {
    event: {
      type: Object,
      default: function () {
        return {};
      }
    }
  },
  computed: {
    fStart () {
      return (new Date(this.event.start.replace(" ", "T")+":00")).toLocaleString()
    }
  }
}
</script>

<style scoped>

</style>
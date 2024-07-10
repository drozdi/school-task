<template>
  <v-list>
    <v-list-group
        v-for="item in menu"
        :key="item.title"
        v-model="item.active"
        :prepend-icon="item.icon"
        no-action
    >
      <template v-slot:activator>
        <v-list-item-content>
          <v-list-item-title v-text="item.title"></v-list-item-title>
        </v-list-item-content>
      </template>
      <v-list-item v-for="child in item.items" :key="child.title" link :href="child.href">
        <v-list-item-content>
          <v-list-item-title><v-icon v-text="child.icon"></v-icon> {{child.title}}</v-list-item-title>
        </v-list-item-content>
      </v-list-item>
    </v-list-group>
  </v-list>
</template>

<script>
export default {
  name: "NavMenu",
  data: () => ({
    menu: [{
      'title': "Main",
      'icon': "mdi-account-circle",
      'items': [{
        'title': "OU",
        'href': "/main/ou/",
        'icon': "mdi-home-group"
      }, {
        'title': "Users",
        'href': "/main/user/",
        'icon': "mdi-account-multiple-outline"
      }, {
        'title': "Groups",
        'href': "/main/group/",
        'icon': "mdi-format-list-group"
      }]
  }, {
      'title': "Предметы/Классы",
      'icon': "mdi-book-account",
      'items': [{
        'title': "Предметы",
        'href': "/cs/subject/",
        'icon': "mdi-attachment-minus"
      }, {
        'title': "Классы",
        'href': "/cs/class/",
        'icon': "mdi-account-group"
      }]
    }, {
      'title': "Расписание",
      'icon': "mdi-calendar-range",
      'href': "/event/"
    }]
  })
}
</script>

<style scoped>

</style>
<template>
    <v-data-table
        dense
        :headers="headers"
        :items="desserts"
        item-key="code"
        class="elevation-1"
        :loading="loading"
        loading-text="Loading... Please wait"
    >
      <template v-slot:top>
        <v-toolbar flat>
          <v-toolbar-title>Пользователи</v-toolbar-title>
          <v-divider class="mx-4" inset vertical></v-divider>
          <v-spacer></v-spacer>
          <v-btn-toggle v-model="ou_id" small mandatory @change="changeOu">
            <v-btn small :value="-1">Все</v-btn>
            <template v-for="(item, index) in ous">
              <!--<v-btn small :value="item.id" :key="index">
                {{item.description}}
                <v-icon v-if="item.items">mdi-chevron-down</v-icon>
              </v-btn>-->
              <v-menu v-if="item.items" offset-y :key="index" open-on-hover>
                <template v-slot:activator="{ on, attrs }">
                  <v-btn small :value="item.id" v-bind="attrs" v-on="on">
                    {{item.description}}
                    <v-icon v-if="item.items">mdi-chevron-down</v-icon>
                  </v-btn>
                </template>
                <v-list>
                  <v-list-item-group v-model="group_id" @change="changeGroup">
                    <v-list-item v-for="(child, i) in item.items" :key="i" :value="child.id">
                      <v-list-item-content>
                        <v-list-item-title>{{child.name}}</v-list-item-title>
                      </v-list-item-content>
                    </v-list-item>
                  </v-list-item-group>
                </v-list>
              </v-menu>
            </template>
            <v-btn small :value="null">Остальные</v-btn>
          </v-btn-toggle>
          <v-spacer></v-spacer>
          <v-btn dark color="light-blue" @click="load">
            Reload
            <v-icon class="ml-2">mdi-reload</v-icon>
          </v-btn>
          <v-dialog v-model="dialogDelete" max-width="500px">
            <v-card>
              <v-card-title class="text-h5">Are you sure you want to delete this item?</v-card-title>
              <v-card-actions>
                <v-spacer></v-spacer>
                <v-btn color="blue darken-1" text @click="closeDelete">Cancel</v-btn>
                <v-btn color="blue darken-1" text @click="deleteItemConfirm">OK</v-btn>
                <v-spacer></v-spacer>
              </v-card-actions>
            </v-card>
          </v-dialog>
        </v-toolbar>
      </template>
      <template v-slot:[`item.actions`]="{ item }">
        <v-btn small text class="mr-2" :to="{ name: 'main-user-edit', params: { login: item.login }}">
          <v-icon>
            mdi-pencil
          </v-icon>
        </v-btn>
        <v-btn small text @click="deleteItem(item)">
          <v-icon>
            mdi-delete
          </v-icon>
        </v-btn>
      </template>
      <template v-slot:[`footer.prepend`]>
        <v-btn color="primary" dark class="mb-2" :to="{ name: 'main-user-new', params: {'ou_id': ou_id} }">
          Добавить
          <v-icon class="ml-2">mdi-plus-box</v-icon>
        </v-btn>
      </template>
    </v-data-table>
</template>

<script>
export default {
  name: "MainUsers",
  data: () => ({
    headers: [{
        text: '#',
        align: 'start',
        value: 'id',
      },
      { text: 'Login', value: 'login' },
      { text: 'Alias', value: 'alias' },
      { text: 'Куратор', value: 'tutor' },
      { text: 'Actions', value: 'actions', sortable: false },
    ],
    ou_id: -1,
    group_id: -1,
    desserts: [],
    loading: true,
    editedIndex: -1,
    editedItem: {},
    dialog: false,
    dialogDelete: false,
    ous: []
  }),
  methods: {
    changeOu () {
      this.group_id = -1;
      this.load();
    },
    changeGroup () {
      this.load();
    },
    load () {
      this.loading = true;
      const formData = new FormData();
      formData.append('f', "list")
      formData.append('ou_id', this.ou_id)
      formData.append('group_id', this.group_id)
      return this.$http.post('/main/user.php', formData).then((function (data) {
        this.desserts = data;
        this.loading = false;
      }).bind(this));
    },
    loadFilter () {
      let formData = new FormData();
      formData.append('f', "load-filters");
      return this.$http.post('/main/user.php', formData).then((function (data) {
        this.ous = data;
      }).bind(this));
    },
    deleteItem (item) {
      this.editedIndex = this.desserts.indexOf(item)
      this.editedItem = Object.assign({}, item)
      this.dialogDelete = true
    },
    deleteItemConfirm () {
      const formData = new FormData();
      formData.append('f', "remove");
      formData.append('id', this.editedItem.id);
      return this.$http.post('/main/user.php', formData).then((function (data) {
        if (data.id === this.editedItem.id) {
          this.desserts.splice(this.editedIndex, 1);
          this.closeDelete();
        }
      }).bind(this));
    },
    closeDelete () {
      this.dialogDelete = false
      this.$nextTick(() => {
        this.editedItem = Object.assign({}, this.defaultItem)
        this.editedIndex = -1
      })
    },
  },
  watch: {
    dialogDelete (val) {
      val || this.closeDelete()
    },
  },
  mounted () {
    this.loadFilter()
      .finally((function () {
        this.load();
      }).bind(this))

  }
}
</script>

<style scoped>

</style>
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
          <v-toolbar-title>Группы</v-toolbar-title>
          <v-divider class="mx-4" inset vertical></v-divider>
          <v-spacer></v-spacer>
          <v-btn-toggle v-model="ou_id" small mandatory @change="load">
            <v-btn small :value="-1">Все</v-btn>
            <v-btn v-for="(ouItem, ouIndex) in ous" small :value="ouItem.id" :key="ouIndex">
              {{ouItem.description}}
            </v-btn>
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
                <v-btn color="blue darken-1" text @click="closeDelete">Отмена</v-btn>
                <v-btn color="blue darken-1" text @click="deleteItemConfirm">OK</v-btn>
                <v-spacer></v-spacer>
              </v-card-actions>
            </v-card>
          </v-dialog>
        </v-toolbar>
      </template>
      <template v-slot:[`item.actions`]="{ item }">
        <v-btn small class="mr-2" text :to="{ name: 'main-group-edit', params: { code: item.code }}">
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
            <v-btn color="primary" dark class="mb-2" :to="{ name: 'main-group-new', params: {'ou_id': ou_id} }">
              Добавить
              <v-icon class="ml-2">mdi-plus-box</v-icon>
            </v-btn>
      </template>
    </v-data-table>
</template>

<script>
export default {
  name: "MainGroups",
  data: () => ({
    headers: [{
        text: '#',
        align: 'start',
        value: 'id',
      },
      { text: 'Название', value: 'name' },
      { text: 'Ответственный', value: 'tutor' },
      { text: 'Code', value: 'code' },
      { text: 'Sort', value: 'sort' },
      { text: 'Actions', value: 'actions', sortable: false },
    ],
    desserts: [],
    ous: [],
    ou_id: -1,
    loading: true,
    dialog: false,
    dialogDelete: false,
    editedIndex: -1,
    editedItem: {
      id: 0,
      code: '',
      name: ''
    },
    defaultItem: {
      id: 0,
      code: '',
      name: ''
    },
  }),
  methods: {
    loadOU () {
      const formData = new FormData();
      formData.append('f', "ous")
      return this.$http.post('/main/group.php', formData).then((function (data) {
        this.ous = data;
      }).bind(this));
    },
    load () {
      this.loading = true;
      const formData = new FormData();
      formData.append('f', "list")
      formData.append('ou_id', this.ou_id)
      return this.$http.post('/main/group.php', formData).then((function (data) {
        this.desserts = data;
        this.loading = false;
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
      return this.$http.post('/main/group.php', formData).then((function (data) {
        if (data.id === this.editedItem.id) {
          let name = this.editedItem.name;
          let code = this.editedItem.code;
          this.$store.dispatch('snackbar/message', 'Группа "' + name + ' (' + code + ')", успешно удалена!');
          this.desserts.splice(this.editedIndex, 1);
          this.closeDelete();
        }
      }).bind(this));
    },
    close () {
      this.dialog = false
      this.$nextTick(() => {
        this.editedItem = Object.assign({}, this.defaultItem)
        this.editedIndex = -1
      })
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
    dialog (val) {
      val || this.close()
    },
    dialogDelete (val) {
      val || this.closeDelete()
    },
  },
  mounted () {
    this.loadOU()
      .finally((function () {
        this.load();
      }).bind(this));
  }
}
</script>

<style scoped>

</style>
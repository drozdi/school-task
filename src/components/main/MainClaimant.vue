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
          <v-toolbar-title>Права</v-toolbar-title>
          <v-divider class="mx-4" inset vertical></v-divider>
          <v-spacer></v-spacer>
          <v-btn dark color="light-blue" @click="load">
            Обновить
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
        <v-btn small text class="mr-2" @click="editItem(item)">
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
        <v-dialog v-model="dialog" max-width="500px">
          <template v-slot:activator="{ on, attrs }">
            <v-btn color="primary" dark class="mb-2" v-bind="attrs" v-on="on">
              Добавить
              <v-icon class="ml-2">mdi-plus-box</v-icon>
            </v-btn>
          </template>
          <v-card>
            <v-card-title>
              <span class="text-h5">{{ formTitle }}</span>
            </v-card-title>
            <v-card-text>
              <v-container>
                <v-row>
                  <v-col cols="12">
                    <v-text-field v-model="editedItem.name" label="Name"></v-text-field>
                  </v-col>
                  <v-col cols="12">
                    <v-text-field v-model="editedItem.code" label="Code"></v-text-field>
                  </v-col>
                </v-row>
              </v-container>
            </v-card-text>
            <v-card-actions>
              <v-spacer></v-spacer>
              <v-btn color="blue darken-1" text @click="close">
                Отмена
              </v-btn>
              <v-btn color="blue darken-1" text @click="save">
                Сохранить
              </v-btn>
            </v-card-actions>
          </v-card>
        </v-dialog>
      </template>
    </v-data-table>
</template>

<script>
export default {
  name: "MainClaimant",
  data: () => ({
    headers: [{
        text: '#',
        align: 'start',
        value: 'id',
      },
      { text: 'Название', value: 'name' },
      { text: 'Code', value: 'code' },
      { text: 'Actions', value: 'actions', sortable: false },
    ],
    desserts: [],
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
    load () {
      this.loading = true;
      const formData = new FormData();
      formData.append('f', "list")
      return this.$http.post('/main/claimant.php', formData).then((function (data) {
        this.desserts = data;
        this.loading = false;
      }).bind(this));
    },
    editItem (item) {
      this.editedIndex = this.desserts.indexOf(item)
      this.editedItem = Object.assign({}, item)
      this.dialog = true
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
      return this.$http.post('/main/claimant.php', formData).then((function (data) {
        if (data.id === this.editedItem.id) {
          let name = this.editedItem.name;
          let code = this.editedItem.code;
          this.$store.dispatch('snackbar/message', 'Право "' + name + ' (' + code + ')", успешно удалено!');
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
    save () {
      const formData = new FormData();
      formData.append('f', "save");
      formData.append('claimant[code]', this.editedItem.code);
      formData.append('claimant[name]', this.editedItem.name);
      if (this.editedIndex > -1) {
        formData.append('claimant[id]', this.editedItem.id);
      }
      return this.$http.post('/main/claimant.php', formData).then((function (data) {
        if (this.editedIndex > -1) {
          Object.assign(this.desserts[this.editedIndex], data);
          this.$store.dispatch('snackbar/message', 'Право "' + data.name + ' (' + data.code + ')", успешно изменено!');
        } else {
          this.editedItem = data;
          this.desserts.push(this.editedItem);
          this.$store.dispatch('snackbar/message', 'Право "' + data.name + ' (' + data.code + ')", успешно созданно!');
        }
        this.close();
      }).bind(this));
    },
  },
  computed: {
    formTitle () {
      return this.editedIndex === -1 ? 'New Claimant' : 'Edit Claimant - '+this.editedItem.name
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
    this.load();
  }
}
</script>

<style scoped>

</style>
<script lang="ts">
  import { onMount } from "svelte";
  import { RestHandler } from "./lib/RestHandler";
  import { DeleteTableRows } from "./lib/DeleteTableRows";

  let task_id;
  let dialog;

  let progress: number = 0;
  let current_amount: number = 0;
  let time_left: number = 0;

  let time = "0";
  let unit = "seconds";
  let interval_starttime;
  let interval;
  let ids;

  let loaded = false;

  let state: "confirming" | "deleting" | "done" = "confirming";

  onMount(() => {
    // get the task id from the url
    const url = new URL(window.location.href);
    task_id = url.searchParams.get("task_id");
  });

  $: if (!loaded && dialog) {
    loaded = true;
    dialog.showModal();
  }

  const start = () => {
    state = "deleting";
    interval_starttime = Date.now();
    interval = setInterval(() => {
      const now = Date.now();
      const diff = now - interval_starttime;
      time = Math.round(diff / 1000) + "";
    }, 1000);
    run();
  };

  const run = async () => {
    const handler = RestHandler.get();
    const data = await handler.pingBulkDelete(task_id);
    progress = data.percentage;
    current_amount = data.current_amount;
    time_left = data.time_left;
    ids = data.details.ids;
    setTimeout(() => {
      if (current_amount > 0) {
        run();
      } else {
        close(true);
      }
    }, 500);
  };

  $: if (+time > 60) {
    time = Math.round(+time / 60) + "";
    unit = "minutes";
  } else {
    unit = "seconds";
  }

  function close(ran = false) {
    state = "done";
    if (ran) {
      clearInterval(interval);
      const url = new URL(window.location.href);
      url.searchParams.delete("task_id");
      // navigate to the current url without the task id
      window.history.replaceState({}, "", url.href);

      // delete rows
      DeleteTableRows.go(ids);
    }

    // close dialog
    setTimeout(() => {
      dialog.close();
    }, 500);
  }
</script>

{#if task_id}
  <dialog bind:this={dialog}>
    <div class="inner">
      {#if state === "confirming"}
        <h2>Are you sure?</h2>
        <p>This action cannot be undone.</p>

        <div class="actions">
          <button class="button button-primary" on:click={start}
            >Yes, delete</button
          >
          <button class="button button-secondary" on:click={close}
            >Cancel</button
          >
        </div>
      {:else if state === "deleting"}
        <h2>Deleting posts...</h2>
        <p>This may take a while. Please do not close this window.</p>
        <span class="runtime">
          <span class="time">{time}</span>
          <span class="unit">{unit}</span>
        </span>
        <div class="progress">
          <div class="progress-bar" style="width: {progress}%;" />
        </div>
      {:else if state === "done"}
        <h2>Done!</h2>
        <p>Deleted posts.</p>
        <div class="actions">
          <button class="button button-primary" on:click={close}>Close</button>
        </div>
      {/if}
    </div>
  </dialog>
{/if}

<style lang="scss">
  $primary: #007cba;
  $secondary: #3e3e3e;

  dialog {
    padding: 15px;
    background: rgba(#202020, 0.6);
    backdrop-filter: blur(5px);
    color: #fff;
    border-radius: 5px;
    border: none;
    max-width: 400px;
    width: 100%;

    h1,
    h2,
    h3,
    h4,
    h5,
    h6 {
      margin: 0;
      color: #efefef;
      font-size: 1.5em;
      font-weight: bold;
    }

    .inner {
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      text-align: center;
    }

    &::backdrop {
      background: rgba(0, 0, 0, 0.2);
      backdrop-filter: blur(2px);
    }

    p {
      margin: 0 0 20px;
    }

    .actions {
      display: flex;
      flex-direction: row;
      align-items: center;
      justify-content: center;
      margin-top: 20px;

      button {
        margin: 0 10px;
      }
    }

    .runtime {
      font-size: 1.5em;
      margin: 0 0 20px;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      text-align: center;
    }

    .progress {
      width: 100%;
      height: 20px;
      background: #3e3e3e;
      border-radius: 5px;
      overflow: hidden;

      .progress-bar {
        height: 100%;
        background: #007cba;
        transition: width 0.5s ease-in-out;
      }
    }

    .button {
      padding: 10px 20px;
      border-radius: 5px;
      border: none;
      cursor: pointer;
      font-size: 1em;
      font-weight: bold;
      transition: all 0.2s ease-in-out;
      &:hover {
        opacity: 0.8;
      }

      &-primary {
        background: $primary;
        color: #fff;
      }

      &-secondary {
        background: $secondary;
        color: #fff;
      }
    }
  }
</style>
